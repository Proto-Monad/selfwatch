<?php

/**
 * selfwatch - development router for PHP's built-in server.
 *
 * Run:  php -S localhost:8003 router.php
 *
 * It maps Sentry SDK ingestion URLs (/api/{id}/store/ and /api/{id}/envelope/) to api.php,
 * serves real files (index.php, ingest.php, assets) directly, and falls back to the Matomo
 * front controller for everything else. In production use an equivalent nginx/Apache rewrite.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Sentry-compatible ingestion endpoints.
if (preg_match('#^/api/(\d+)/(store|envelope)/?$#', (string) $path, $m)) {
    $_SERVER['SW_SENTRY_PROJECT']  = $m[1];
    $_SERVER['SW_SENTRY_ENDPOINT'] = $m[2];
    require __DIR__ . '/api.php';
    return true;
}

// Let the built-in server serve existing files (php scripts get executed) as-is.
if ($path !== '/' && file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false;
}

// Everything else: the application front controller.
require __DIR__ . '/index.php';
return true;
