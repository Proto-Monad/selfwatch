<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs;

/**
 * Scheduled tasks for the Logs plugin.
 *
 * Alert evaluation runs here (off the ingest request path) rather than synchronously during
 * ingestion. The Matomo task scheduler runs at most hourly; for tighter alert latency, point
 * a system cron at `./console logs:process-alerts` (e.g. every minute) instead - both call the
 * same cursor-based {@see AlertEngine::processNew()} and are safe to run concurrently-ish
 * because the cursor only ever advances.
 */
class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        $this->hourly('processAlerts', null, self::LOWEST_PRIORITY);
    }

    public function processAlerts(): void
    {
        (new AlertEngine())->processNew();
    }
}
