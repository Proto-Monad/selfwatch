<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * Sentry-compatible ingestion endpoint. Accepts events from Sentry SDKs:
 *
 *   POST /api/{PROJECT_ID}/store/      body = a single JSON event
 *   POST /api/{PROJECT_ID}/envelope/   body = a Sentry envelope (NDJSON)
 *
 * Auth: the project's ingest token is the DSN public key (sentry_key). It may be sent in the
 * `X-Sentry-Auth` header, as `?sentry_key=...`, or as `Authorization: Bearer <token>`. A
 * selfwatch DSN therefore looks like:  http(s)://<ingest-token>@<host>/<PROJECT_ID>
 *
 * Clean `/api/{id}/store/` URLs require a rewrite. For PHP's built-in server use the bundled
 * router:  php -S localhost:8003 router.php   (see router.php). Production: rewrite to api.php.
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Application\Environment;
use Piwik\Plugins\Logs\IngestLogger;
use Piwik\Plugins\Logs\Model;
use Piwik\Plugins\Logs\SentryIngest;

define('PIWIK_DOCUMENT_ROOT', __DIR__);
define('PIWIK_INCLUDE_PATH', __DIR__);
define('PIWIK_ENABLE_DISPATCH', false);

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

const SW_API_MAX_BODY_BYTES     = 2097152; // 2 MiB
const SW_API_RATE_LIMIT_PER_MIN = 3000;

header('Content-Type: application/json');

$log = new IngestLogger();

function sw_api_fail(IngestLogger $log, int $code, string $message, array $headers = []): void
{
    foreach ($headers as $h) {
        header($h);
    }
    http_response_code($code);
    $log->warning('rejected', ['code' => $code, 'message' => $message]);
    echo json_encode(['detail' => $message]);
    exit;
}

/** Resolve the DSN public key (= ingest token) from headers or query string. */
function sw_api_key(): string
{
    $headers = function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [];

    if (!empty($headers['x-sentry-auth']) && preg_match('/sentry_key=([0-9a-zA-Z]+)/', $headers['x-sentry-auth'], $m)) {
        return $m[1];
    }
    if (!empty($headers['authorization']) && stripos($headers['authorization'], 'bearer ') === 0) {
        return trim(substr($headers['authorization'], 7));
    }
    if (!empty($_GET['sentry_key'])) {
        return (string) $_GET['sentry_key'];
    }
    return '';
}

/** True for an insecure (plain-HTTP) request that is not loopback. */
function sw_api_insecure(): bool
{
    $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
    if ($https) {
        return false;
    }
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    return !(in_array($remote, ['127.0.0.1', '::1'], true) || strpos($remote, '127.') === 0);
}

// --- resolve project id + endpoint (set by router.php, or parsed from the URL / query) -----
$projectId = (int) ($_SERVER['SW_SENTRY_PROJECT'] ?? Piwik\Common::getRequestVar('project_id', 0, 'int'));
$endpoint  = (string) ($_SERVER['SW_SENTRY_ENDPOINT'] ?? Piwik\Common::getRequestVar('endpoint', 'store', 'string'));
if (!$projectId && preg_match('#/api/(\d+)/(store|envelope)/?#', (string) ($_SERVER['REQUEST_URI'] ?? ''), $m)) {
    $projectId = (int) $m[1];
    $endpoint  = $m[2];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sw_api_fail($log, 405, 'Use POST.');
}
if (sw_api_insecure()) {
    sw_api_fail($log, 400, 'Ingestion requires an HTTPS connection.');
}

$environment = new Environment(null);
$environment->init();

$log->info('request', ['endpoint' => $endpoint, 'project' => $projectId, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '?']);

$token = sw_api_key();
if ($token === '') {
    sw_api_fail($log, 401, 'Missing sentry_key / ingest token.');
}

$model     = new Model();
$tokenSite = $model->getSiteIdForToken($token);
if (!$tokenSite) {
    sw_api_fail($log, 403, 'Invalid ingest token.');
}
// The URL project id (if given) must match the token's project.
if ($projectId && $projectId !== $tokenSite) {
    sw_api_fail($log, 403, 'Ingest token does not match the project in the URL.');
}
$idSite = (int) $tokenSite;

if (!$model->checkRateLimit($idSite, SW_API_RATE_LIMIT_PER_MIN)) {
    sw_api_fail($log, 429, 'Rate limit exceeded.', ['Retry-After: 60']);
}

$body = file_get_contents('php://input', false, null, 0, SW_API_MAX_BODY_BYTES + 1);
if ($body !== false && strlen($body) > SW_API_MAX_BODY_BYTES) {
    sw_api_fail($log, 413, 'Payload too large.');
}
$body = (string) $body;

$ingest = new SentryIngest($log);

try {
    if ($endpoint === 'envelope') {
        $eventId = $ingest->ingestEnvelope($idSite, $body);
    } else {
        $event = json_decode($body, true);
        if (!is_array($event)) {
            sw_api_fail($log, 400, 'Request body is not a valid JSON event.');
        }
        $eventId = $ingest->ingestEvent($idSite, $event);
    }
} catch (\Throwable $e) {
    $log->error('ingest failed', ['error' => $e->getMessage()]);
    sw_api_fail($log, 500, 'Failed to store event.');
}

http_response_code(200);
echo json_encode(['id' => $eventId]);
