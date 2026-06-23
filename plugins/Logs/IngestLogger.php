<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs;

use Piwik\Config;

/**
 * Verbose, structured logging of the ingestion pipeline (receive -> auth -> validate ->
 * store). Each request gets a short correlation id so a single ingest can be followed end
 * to end. Warnings and errors are always written; debug/info lines are written only when
 * verbose ingest logging is enabled.
 *
 * Enable verbose logging in config/config.ini.php:
 *
 *     [Logs]
 *     verbose_ingest = 1
 *
 * Lines are appended to tmp/logs/sw_ingest.log as:
 *     2026-06-20T12:00:00+00:00  DEBUG  [a1b2c3d4]  message  {"json":"context"}
 */
class IngestLogger
{
    private const LEVELS = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];

    /** @var string */
    private $requestId;
    /** @var bool|null lazily resolved so it works regardless of when the logger is built */
    private $verbose = null;
    /** @var string */
    private $file;

    public function __construct()
    {
        $this->requestId = substr(bin2hex(random_bytes(4)), 0, 8);
        $this->file      = PIWIK_USER_PATH . '/tmp/logs/sw_ingest.log';
    }

    private function isVerbose(): bool
    {
        // Resolved lazily: the logger may be constructed before Config is initialised.
        if ($this->verbose === null) {
            $value = $this->config('verbose_ingest');
            $this->verbose = $value === null ? null : (bool) $value;
            if ($this->verbose === null) {
                return false; // config not ready yet; re-check on the next call
            }
        }
        return $this->verbose;
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function debug(string $message, array $context = []): void
    {
        $this->write('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    /**
     * Log a list of validation issues at info level (no-op when there are none).
     *
     * @param string[] $issues
     */
    public function issues(array $issues): void
    {
        foreach ($issues as $issue) {
            $this->info('validation: ' . $issue);
        }
    }

    private function write(string $level, string $message, array $context): void
    {
        // Below warning, only write when verbose logging is enabled.
        if (self::LEVELS[$level] < self::LEVELS['warning'] && !$this->isVerbose()) {
            return;
        }

        $line = sprintf(
            "%s  %-7s [%s]  %s%s\n",
            date('c'),
            strtoupper($level),
            $this->requestId,
            $message,
            $context ? '  ' . json_encode($context, JSON_UNESCAPED_SLASHES) : ''
        );

        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        @file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }

    private function config(string $key)
    {
        try {
            $section = Config::getInstance()->Logs;
            return $section[$key] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
