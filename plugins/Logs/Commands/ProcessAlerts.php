<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Logs\AlertEngine;

/**
 * Evaluate alert rules against newly-ingested log entries. Designed to be run from a system
 * cron at whatever cadence you want alert latency to be, e.g. every minute:
 *
 *     * * * * * php /path/to/selfwatch/console logs:process-alerts >/dev/null 2>&1
 *
 * It is cursor-based, so each run only looks at entries ingested since the previous run.
 */
class ProcessAlerts extends ConsoleCommand
{
    protected function configure(): void
    {
        $this->setName('logs:process-alerts');
        $this->setDescription('Evaluate alert rules against newly-ingested log entries (run via cron for low-latency alerts).');
        $this->addOptionalValueOption('batch', null, 'Maximum entries to process in one run.', 5000);
    }

    protected function doExecute(): int
    {
        $batch     = (int) $this->getInput()->getOption('batch');
        $processed = (new AlertEngine())->processNew($batch > 0 ? $batch : 5000);

        $this->getOutput()->writeln(sprintf('Processed %d new log %s for alerts.', $processed, $processed === 1 ? 'entry' : 'entries'));

        return self::SUCCESS;
    }
}
