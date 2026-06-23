<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs;

/**
 * The Logs plugin is the heart of selfwatch: it stores, queries and displays the log
 * entries ingested from external applications (PHP, Python, Rust, JavaScript, ...).
 *
 * The log_entry table itself is part of the core schema; this plugin provides the data
 * model, the ingestion handling, and the viewer UI.
 */
class Logs extends \Piwik\Plugin
{
    public function registerEvents(): array
    {
        return [
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
        ];
    }

    public function getStylesheetFiles(&$stylesheets): void
    {
        $stylesheets[] = 'plugins/Logs/stylesheets/logs.less';
    }
}
