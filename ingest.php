<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * Log ingestion endpoint. This replaces Matomo's tracker (matomo.php/piwik.php): instead of
 * recording analytics hits, it accepts structured log entries from any source over HTTP.
 *
 * Auth:   per-project ingest token, sent as `Authorization: Bearer <token>`,
 *         an `X-Sw-Token: <token>` header, or a `?token=<token>` query parameter.
 * Body:   JSON. A single entry object, a bare array of entries, or {"logs": [ ... ]}.
 *
 * Entry fields: message (required), level, source, context (object), trace_id, host,
 *               timestamp (unix or ISO-8601; defaults to now).
 *
 * @link    https://github.com/selfwatch/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Application\Environment;
use Piwik\Plugins\Logs\Model;

define('PIWIK_DOCUMENT_ROOT', __DIR__);
define('PIWIK_INCLUDE_PATH', __DIR__);
define('PIWIK_ENABLE_DISPATCH', false);

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

// Request bounds (defence against storage-exhaustion / oversized payloads).
const SW_MAX_BODY_BYTES = 2097152; // 2 MiB
const SW_MAX_ENTRIES    = 1000;    // entries per request
const SW_RATE_LIMIT_PER_MIN = 3000; // ingest requests per project per minute

header('Content-Type: application/json');

function sw_ingest_fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

function sw_ingest_token(): string
{
    // Token is accepted via headers only - never a query parameter - so it does not leak
    // into web-server access logs, proxies, or browser history.
    $headers = function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [];

    if (!empty($headers['authorization']) && stripos($headers['authorization'], 'bearer ') === 0) {
        return trim(substr($headers['authorization'], 7));
    }
    if (!empty($headers['x-sw-token'])) {
        return trim($headers['x-sw-token']);
    }
    return '';
}

/**
 * True when the request did not arrive over a secure (HTTPS) connection and is not from
 * the local loopback (so local development over http keeps working).
 */
function sw_ingest_is_insecure(): bool
{
    $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
    if ($https) {
        return false;
    }
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $isLoopback = in_array($remote, ['127.0.0.1', '::1'], true) || strpos($remote, '127.') === 0;
    return !$isLoopback;
}

/**
 * Normalise the decoded body into a flat list of entry arrays.
 *
 * @return array<int,array<string,mixed>>
 */
function sw_ingest_entries($data): array
{
    if (!is_array($data)) {
        return [];
    }
    if (isset($data['logs']) && is_array($data['logs'])) {
        $data = $data['logs'];
    }
    // A single entry object (associative) vs a list of entries.
    $isList = array_keys($data) === range(0, count($data) - 1);
    $entries = $isList ? $data : [$data];

    return array_values(array_filter($entries, static function ($e) {
        return is_array($e) && isset($e['message']) && $e['message'] !== '';
    }));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sw_ingest_fail(405, 'Use POST to ingest logs.');
}

// Tokens must not travel in clear: reject plain-HTTP ingestion from non-loopback clients.
if (sw_ingest_is_insecure()) {
    sw_ingest_fail(400, 'Ingestion requires an HTTPS connection.');
}

$environment = new Environment(null);
$environment->init();

$token = sw_ingest_token();
if ($token === '') {
    sw_ingest_fail(401, 'Missing ingest token.');
}

$model  = new Model();
$idSite = $model->getSiteIdForToken($token);
if (!$idSite) {
    sw_ingest_fail(403, 'Invalid ingest token.');
}

// Per-project rate limit (fixed window). Defends against a leaked token flooding the store.
if (!$model->checkRateLimit((int) $idSite, SW_RATE_LIMIT_PER_MIN)) {
    sw_ingest_fail(429, 'Rate limit exceeded for this project. Slow down.');
}

// Bound the request to keep a single project from exhausting storage/memory.
$rawBody = file_get_contents('php://input', false, null, 0, SW_MAX_BODY_BYTES + 1);
if ($rawBody !== false && strlen($rawBody) > SW_MAX_BODY_BYTES) {
    sw_ingest_fail(413, 'Request body too large.');
}

$data = json_decode((string) $rawBody, true);
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    sw_ingest_fail(400, 'Request body is not valid JSON.');
}

$entries = sw_ingest_entries($data);
if (empty($entries)) {
    sw_ingest_fail(400, 'No log entries with a message found.');
}
if (count($entries) > SW_MAX_ENTRIES) {
    sw_ingest_fail(413, 'Too many log entries in one request (max ' . SW_MAX_ENTRIES . ').');
}

// Stamp the origin host if the client did not provide one.
$remoteHost = $_SERVER['REMOTE_ADDR'] ?? null;
foreach ($entries as &$entry) {
    if (empty($entry['host']) && $remoteHost) {
        $entry['host'] = $remoteHost;
    }
}
unset($entry);

try {
    $stored  = $model->insertBatch((int) $idSite, $entries);
    $written = count($stored);
} catch (\Throwable $e) {
    sw_ingest_fail(500, 'Failed to store log entries.');
}

// Alert rules are evaluated asynchronously (off the ingest request path) by the
// `logs:process-alerts` command / scheduled task, so ingestion stays fast and is never
// coupled to webhook/email availability.

http_response_code(202);
echo json_encode(['status' => 'accepted', 'accepted' => $written]);
