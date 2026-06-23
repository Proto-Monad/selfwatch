<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * Self-contained test runner for the selfwatch-specific logic (no PHPUnit dependency, since
 * this packaged build ships without the dev toolchain). Run from the app root:
 *
 *     php plugins/Logs/tests/run.php
 *
 * Exits non-zero if any assertion fails. Uses high, throwaway project ids (99xx) and cleans
 * up everything it writes, so it is safe to run against a populated database.
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs\Tests;

use Piwik\Application\Environment;
use Piwik\Option;
use Piwik\Plugins\Logs\AlertModel;
use Piwik\Plugins\Logs\EventValidator;
use Piwik\Plugins\Logs\Model;

if (!defined('PIWIK_DOCUMENT_ROOT')) {
    define('PIWIK_DOCUMENT_ROOT', __DIR__ . '/../../..');
    define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
    define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);
    define('PIWIK_ENABLE_DISPATCH', false);
}
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_PORT'] = '80';
require PIWIK_INCLUDE_PATH . '/core/bootstrap.php';
(new Environment(null))->init();

// ---- tiny assert harness ----------------------------------------------------------------
$GLOBALS['_pass'] = 0;
$GLOBALS['_fail'] = 0;
$GLOBALS['_group'] = '';
function group(string $g): void { $GLOBALS['_group'] = $g; echo "\n# $g\n"; }
function ok(bool $cond, string $what): void {
    if ($cond) { $GLOBALS['_pass']++; echo "  ✓ $what\n"; }
    else { $GLOBALS['_fail']++; echo "  ✗ FAIL: $what\n"; }
}
function eq($expected, $actual, string $what): void {
    ok($expected === $actual, $what . ($expected === $actual ? '' : "  (expected " . var_export($expected, true) . ", got " . var_export($actual, true) . ')'));
}

// =========================================================================================
group('EventValidator - untrusted Sentry event normalisation');
$v = new EventValidator();

// event_id: dashed valid uuid -> 32 lowercase hex, kept
$r = $v->normalize(['event_id' => '550E8400-E29B-41D4-A716-446655440000', 'message' => 'x']);
eq('550e8400e29b41d4a716446655440000', $r['entry']['event_id'], 'valid dashed uuid normalised to 32-hex');
// event_id: junk -> generated 32-hex + issue
$r = $v->normalize(['event_id' => 'not-an-id', 'message' => 'x']);
ok((bool) preg_match('/^[0-9a-f]{32}$/', $r['entry']['event_id']), 'invalid event_id regenerated to 32-hex');
ok(in_array('event_id was not a 32-char hex uuid; generated a new one', $r['issues'], true), 'invalid event_id recorded as an issue');

// level mapping
eq('critical', $v->normalize(['level' => 'fatal', 'message' => 'x'])['entry']['level'], 'level fatal -> critical');
eq('warning', $v->normalize(['level' => 'warn', 'message' => 'x'])['entry']['level'], 'level warn -> warning');
eq('warning', $v->normalize(['level' => 'WARNING', 'message' => 'x'])['entry']['level'], 'level WARNING -> warning (case)');
eq('error', $v->normalize(['level' => 'bogus', 'message' => 'x'])['entry']['level'], 'unknown level -> error');
eq('info', $v->normalize(['level' => 'info', 'message' => 'x'])['entry']['level'], 'valid level passthrough');

// message extraction
eq('hello', $v->normalize(['message' => 'hello'])['entry']['message'], 'string message');
eq('F', $v->normalize(['message' => ['formatted' => 'F', 'message' => 'M']])['entry']['message'], 'object message prefers formatted');
eq('M', $v->normalize(['message' => ['message' => 'M']])['entry']['message'], 'object message falls back to message');
eq('RuntimeException: boom', $v->normalize(['exception' => ['values' => [['type' => 'RuntimeException', 'value' => 'boom']]]])['entry']['message'], 'message derived from exception');
$r = $v->normalize([]);
eq('', $r['entry']['message'], 'no message -> empty');
ok(in_array('event had no message; stored empty', $r['issues'], true), 'empty message recorded as an issue');
// message truncation
$long = str_repeat('a', EventValidator::MAX_MESSAGE + 50);
$r = $v->normalize(['message' => $long]);
eq(EventValidator::MAX_MESSAGE, strlen($r['entry']['message']), 'over-long message truncated to MAX_MESSAGE');
ok(count(array_filter($r['issues'], fn($i) => strpos($i, 'message truncated') === 0)) === 1, 'message truncation recorded as an issue');

// tags: cap count + truncate oversized (context is stored as a JSON string)
$ctxOf = function (array $r): array {
    $c = $r['entry']['context'];
    return is_array($c) ? $c : (array) json_decode((string) $c, true);
};
$manyTags = [];
for ($i = 0; $i < EventValidator::MAX_TAGS + 5; $i++) { $manyTags["k$i"] = "v$i"; }
$r = $v->normalize(['message' => 'x', 'tags' => $manyTags]);
ok(count($ctxOf($r)['tags']) <= EventValidator::MAX_TAGS, 'tag count capped at MAX_TAGS');
ok(count(array_filter($r['issues'], fn($i) => strpos($i, 'too many tags') === 0)) === 1, 'tag overflow recorded as an issue');
$r = $v->normalize(['message' => 'x', 'tags' => ['k' => str_repeat('z', EventValidator::MAX_TAG_VALUE + 100)]]);
ok(strlen($ctxOf($r)['tags']['k']) <= EventValidator::MAX_TAG_VALUE, 'oversized tag value truncated');

// source precedence + trace + defaults
eq('mylogger', $v->normalize(['message' => 'x', 'logger' => 'mylogger', 'platform' => 'php'])['entry']['source'], 'source prefers logger');
eq('POST /pay', $v->normalize(['message' => 'x', 'transaction' => 'POST /pay', 'platform' => 'php'])['entry']['source'], 'source falls back to transaction');
eq('php', $v->normalize(['message' => 'x', 'platform' => 'php'])['entry']['source'], 'source falls back to platform');
$r = $v->normalize(['event_id' => str_repeat('a', 32), 'message' => 'x']);
eq($r['entry']['event_id'], $r['entry']['trace_id'], 'trace_id falls back to event_id when no trace context');
eq('production', $v->normalize(['message' => 'x'])['entry']['environment'], 'environment defaults to production');
eq('event', $v->normalize(['message' => 'x'])['entry']['kind'], 'kind is "event"');

// =========================================================================================
group('Model - level normalisation & severity ranking');
$m = new Model();
eq('critical', $m->normaliseLevel('fatal'), 'storage level fatal -> critical');
eq('warning', $m->normaliseLevel('warn'), 'storage level warn -> warning');
eq('warning', $m->normaliseLevel('  WARN '), 'storage level trims + lowercases');
eq('info', $m->normaliseLevel('totally-unknown'), 'storage unknown level -> info');
ok($m->levelRank('critical') > $m->levelRank('error'), 'rank critical > error');
ok($m->levelRank('error') > $m->levelRank('warning'), 'rank error > warning');
ok($m->levelRank('warning') > $m->levelRank('info'), 'rank warning > info');

// =========================================================================================
group('Ingest tokens - hashed storage, resolution & isolation');
$S1 = 9991; $S2 = 9992;
Option::delete('Logs_ingest_token_' . $S1);
Option::delete('Logs_ingest_token_' . $S2);
$tok1 = $m->regenerateToken($S1);
ok(strlen($tok1) >= 32, 'regenerateToken returns a long plaintext token');
ok($m->tokenExists($S1), 'tokenExists true after generation');
eq($S1, $m->getSiteIdForToken($tok1), 'token resolves to its own project');
eq(null, $m->getSiteIdForToken('definitely-not-a-real-token'), 'unknown token resolves to null');
eq(null, $m->getSiteIdForToken(''), 'empty token resolves to null');
$stored = Option::get('Logs_ingest_token_' . $S1);
ok($stored !== $tok1 && strlen((string) $stored) === 64, 'only the sha-256 hash is stored, never the plaintext');

// =========================================================================================
group('IDOR - getEntry is scoped to the project');
$rows = $m->insertBatch($S1, [['level' => 'error', 'message' => 'idor-probe', 'source' => 'test']]);
$id = (int) $rows[0]['idlogentry'];
ok($m->getEntry($S1, $id) !== null, 'owner project can read its own entry');
eq(null, $m->getEntry($S2, $id), 'another project CANNOT read it (IDOR-safe)');

// keyset pagination sanity
$rows2 = $m->insertBatch($S1, [['level' => 'info', 'message' => 'newer', 'source' => 'test']]);
$id2 = (int) $rows2[0]['idlogentry'];
$older = $m->getLogs($S1, ['before_id' => $id2], 50);
ok(count(array_filter($older, fn($e) => (int) $e['idlogentry'] === $id)) === 1, 'before-cursor returns older entry');
ok(count(array_filter($older, fn($e) => (int) $e['idlogentry'] === $id2)) === 0, 'before-cursor excludes the cursor row itself');

// =========================================================================================
group('IDOR - alert rules are scoped to the project');
$am = new AlertModel();
$idRule = $am->createRule($S1, ['name' => 'test-rule', 'enabled' => 1, 'min_level' => 'error', 'source' => '', 'message_contains' => '', 'channel' => 'webhook', 'channel_target' => 'https://example.com/hook', 'cooldown_seconds' => 0]);
ok($idRule > 0, 'createRule returns an id');
ok($am->getRule($idRule, $S1) !== null, 'owner project reads its rule');
eq(null, $am->getRule($idRule, $S2), 'another project CANNOT read the rule (IDOR-safe)');
$am->deleteRule($idRule, $S2); // cross-project delete must be a no-op
ok($am->getRule($idRule, $S1) !== null, 'cross-project deleteRule is a no-op');

// ---- cleanup ----------------------------------------------------------------------------
$am->deleteRule($idRule, $S1);
$m->deleteLogsForSite($S1);
$m->deleteLogsForSite($S2);
Option::delete('Logs_ingest_token_' . $S1);
Option::delete('Logs_ingest_token_' . $S2);

// ---- summary ----------------------------------------------------------------------------
echo "\n" . str_repeat('-', 48) . "\n";
printf("Result: %d passed, %d failed\n", $GLOBALS['_pass'], $GLOBALS['_fail']);
exit($GLOBALS['_fail'] === 0 ? 0 : 1);
