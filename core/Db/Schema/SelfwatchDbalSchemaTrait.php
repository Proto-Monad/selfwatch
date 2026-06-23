<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Schema;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema as DbalSchema;
use Doctrine\DBAL\Schema\Table as DbalTable;
use Exception;
use Piwik\Concurrency\Lock;
use Piwik\Plugins\UsersManager\Model;

/**
 * Shared, engine-agnostic schema definition for selfwatch.
 *
 * The framework table set is described once here as Doctrine DBAL {@see DbalTable} builders;
 * the concrete schema classes (SQLite, MySQL/MariaDB) only supply their DBAL platform via
 * {@see getDbalPlatform()}, and DBAL emits the platform-specific DDL. No SQL is written or
 * translated by hand, and both engines stay in lockstep from a single source of truth.
 */
trait SelfwatchDbalSchemaTrait
{
    /** The DBAL platform whose DDL dialect this engine speaks. */
    abstract protected function getDbalPlatform(): AbstractPlatform;

    protected function tableDefinitions(): array
    {
        $tokenDescLen = Model::MAX_LENGTH_TOKEN_DESCRIPTION;
        $lockKeyLen   = Lock::MAX_KEY_LEN;

        return [
            'user' => function (DbalTable $t): void {
                $t->addColumn('login', 'string', ['length' => 100]);
                $t->addColumn('password', 'string', ['length' => 255]);
                $t->addColumn('email', 'string', ['length' => 100]);
                $t->addColumn('twofactor_secret', 'string', ['length' => 40, 'default' => '']);
                $t->addColumn('superuser_access', 'smallint', ['default' => 0]);
                $t->addColumn('date_registered', 'datetime', ['notnull' => false]);
                $t->addColumn('ts_password_modified', 'datetime', ['notnull' => false]);
                $t->addColumn('idchange_last_viewed', 'integer', ['notnull' => false]);
                $t->addColumn('invited_by', 'string', ['length' => 100, 'notnull' => false]);
                $t->addColumn('invite_token', 'string', ['length' => 191, 'notnull' => false]);
                $t->addColumn('invite_link_token', 'string', ['length' => 191, 'notnull' => false]);
                $t->addColumn('invite_expired_at', 'datetime', ['notnull' => false]);
                $t->addColumn('invite_accept_at', 'datetime', ['notnull' => false]);
                $t->addColumn('ts_changes_shown', 'datetime', ['notnull' => false]);
                $t->addColumn('ts_last_seen', 'datetime', ['notnull' => false]);
                $t->addColumn('ts_inactivity_notified', 'datetime', ['notnull' => false]);
                $t->setPrimaryKey(['login']);
                $t->addUniqueIndex(['email']);
            },

            'user_token_auth' => function (DbalTable $t) use ($tokenDescLen): void {
                $t->addColumn('idusertokenauth', 'bigint', ['autoincrement' => true]);
                $t->addColumn('login', 'string', ['length' => 100]);
                $t->addColumn('description', 'string', ['length' => $tokenDescLen]);
                $t->addColumn('password', 'string', ['length' => 191]);
                $t->addColumn('hash_algo', 'string', ['length' => 30]);
                $t->addColumn('system_token', 'smallint', ['default' => 0]);
                $t->addColumn('last_used', 'datetime', ['notnull' => false]);
                $t->addColumn('date_created', 'datetime');
                $t->addColumn('date_expired', 'datetime', ['notnull' => false]);
                $t->addColumn('secure_only', 'smallint', ['default' => 0]);
                $t->addColumn('ts_rotation_notified', 'datetime', ['notnull' => false]);
                $t->addColumn('ts_expiration_warning_notified', 'datetime', ['notnull' => false]);
                $t->setPrimaryKey(['idusertokenauth']);
                $t->addUniqueIndex(['password']);
            },

            'twofactor_recovery_code' => function (DbalTable $t): void {
                $t->addColumn('idrecoverycode', 'bigint', ['autoincrement' => true]);
                $t->addColumn('login', 'string', ['length' => 100]);
                $t->addColumn('recovery_code', 'string', ['length' => 40]);
                $t->setPrimaryKey(['idrecoverycode']);
            },

            'access' => function (DbalTable $t): void {
                $t->addColumn('idaccess', 'integer', ['autoincrement' => true]);
                $t->addColumn('login', 'string', ['length' => 100]);
                $t->addColumn('idsite', 'integer');
                $t->addColumn('access', 'string', ['length' => 50, 'notnull' => false]);
                $t->setPrimaryKey(['idaccess']);
                $t->addIndex(['login', 'idsite']);
            },

            'site' => function (DbalTable $t): void {
                $t->addColumn('idsite', 'integer', ['autoincrement' => true]);
                $t->addColumn('name', 'string', ['length' => 90]);
                $t->addColumn('description', 'string', ['length' => 255, 'default' => '']);
                $t->addColumn('main_url', 'string', ['length' => 255]);
                $t->addColumn('ts_created', 'datetime', ['notnull' => false]);
                $t->addColumn('ecommerce', 'smallint', ['default' => 0]);
                $t->addColumn('sitesearch', 'smallint', ['default' => 1]);
                $t->addColumn('sitesearch_keyword_parameters', 'text');
                $t->addColumn('sitesearch_category_parameters', 'text');
                $t->addColumn('timezone', 'string', ['length' => 50]);
                $t->addColumn('currency', 'string', ['length' => 3]);
                $t->addColumn('exclude_unknown_urls', 'smallint', ['default' => 0]);
                $t->addColumn('excluded_ips', 'text');
                $t->addColumn('excluded_parameters', 'text');
                $t->addColumn('excluded_user_agents', 'text');
                $t->addColumn('excluded_referrers', 'text');
                $t->addColumn('`group`', 'string', ['length' => 250]);
                $t->addColumn('`type`', 'string', ['length' => 255]);
                $t->addColumn('keep_url_fragment', 'smallint', ['default' => 0]);
                $t->addColumn('creator_login', 'string', ['length' => 100, 'notnull' => false]);
                $t->setPrimaryKey(['idsite']);
            },

            'site_setting' => function (DbalTable $t): void {
                $t->addColumn('idsite', 'integer');
                $t->addColumn('plugin_name', 'string', ['length' => 60]);
                $t->addColumn('setting_name', 'string', ['length' => 255]);
                $t->addColumn('setting_value', 'text');
                $t->addColumn('json_encoded', 'smallint', ['default' => 0]);
                $t->addColumn('idsite_setting', 'bigint', ['autoincrement' => true]);
                $t->setPrimaryKey(['idsite_setting']);
                $t->addIndex(['idsite', 'plugin_name']);
            },

            'site_url' => function (DbalTable $t): void {
                $t->addColumn('idsite', 'integer');
                $t->addColumn('url', 'string', ['length' => 190]);
                $t->setPrimaryKey(['idsite', 'url']);
            },

            'plugin_setting' => function (DbalTable $t): void {
                $t->addColumn('plugin_name', 'string', ['length' => 60]);
                $t->addColumn('setting_name', 'string', ['length' => 255]);
                $t->addColumn('setting_value', 'text');
                $t->addColumn('json_encoded', 'smallint', ['default' => 0]);
                $t->addColumn('user_login', 'string', ['length' => 100, 'default' => '']);
                $t->addColumn('idplugin_setting', 'bigint', ['autoincrement' => true]);
                $t->setPrimaryKey(['idplugin_setting']);
                $t->addIndex(['plugin_name', 'user_login']);
            },

            'option' => function (DbalTable $t): void {
                $t->addColumn('option_name', 'string', ['length' => 191]);
                $t->addColumn('option_value', 'text');
                $t->addColumn('autoload', 'smallint', ['default' => 1]);
                $t->setPrimaryKey(['option_name']);
                $t->addIndex(['autoload']);
            },

            'session' => function (DbalTable $t): void {
                $t->addColumn('id', 'string', ['length' => 191]);
                $t->addColumn('modified', 'integer', ['notnull' => false]);
                $t->addColumn('lifetime', 'integer', ['notnull' => false]);
                $t->addColumn('data', 'text', ['notnull' => false]);
                $t->setPrimaryKey(['id']);
            },

            'sequence' => function (DbalTable $t): void {
                $t->addColumn('name', 'string', ['length' => 120]);
                $t->addColumn('value', 'bigint');
                $t->setPrimaryKey(['name']);
            },

            'brute_force_log' => function (DbalTable $t): void {
                $t->addColumn('id_brute_force_log', 'bigint', ['autoincrement' => true]);
                $t->addColumn('ip_address', 'string', ['length' => 60, 'notnull' => false]);
                $t->addColumn('attempted_at', 'datetime');
                $t->addColumn('login', 'string', ['length' => 100, 'notnull' => false]);
                $t->setPrimaryKey(['id_brute_force_log']);
                $t->addIndex(['ip_address']);
            },

            // Framework table the core admin UI reads (system summary / failures widget).
            'tracking_failure' => function (DbalTable $t): void {
                $t->addColumn('idsite', 'bigint', ['unsigned' => true]);
                $t->addColumn('idfailure', 'smallint', ['unsigned' => true]);
                $t->addColumn('date_first_occurred', 'datetime');
                $t->addColumn('request_url', 'text');
                $t->setPrimaryKey(['idsite', 'idfailure']);
            },

            'locks' => function (DbalTable $t) use ($lockKeyLen): void {
                $t->addColumn('`key`', 'string', ['length' => $lockKeyLen]);
                $t->addColumn('`value`', 'string', ['length' => 255, 'notnull' => false]);
                $t->addColumn('expiry_time', 'bigint', ['default' => 9999999999]);
                $t->setPrimaryKey(['`key`']);
            },

            'changes' => function (DbalTable $t): void {
                $t->addColumn('idchange', 'integer', ['autoincrement' => true]);
                $t->addColumn('created_time', 'datetime');
                $t->addColumn('plugin_name', 'string', ['length' => 60]);
                $t->addColumn('version', 'string', ['length' => 20]);
                $t->addColumn('title', 'string', ['length' => 255]);
                $t->addColumn('description', 'text', ['notnull' => false]);
                $t->addColumn('link_name', 'string', ['length' => 255, 'notnull' => false]);
                $t->addColumn('link', 'string', ['length' => 255, 'notnull' => false]);
                $t->setPrimaryKey(['idchange']);
                $t->addUniqueIndex(['plugin_name', 'version', 'title']);
            },

            'annotations' => function (DbalTable $t): void {
                $t->addColumn('id', 'bigint', ['autoincrement' => true]);
                $t->addColumn('idsite', 'integer');
                $t->addColumn('date', 'datetime');
                $t->addColumn('note', 'text');
                $t->addColumn('starred', 'smallint', ['default' => 0]);
                $t->addColumn('user', 'string', ['length' => 100]);
                $t->setPrimaryKey(['id']);
                $t->addIndex(['idsite', 'date']);
            },

            // selfwatch application log (this platform's own log records).
            'logger_message' => function (DbalTable $t): void {
                $t->addColumn('idlogger_message', 'integer', ['autoincrement' => true]);
                $t->addColumn('tag', 'string', ['length' => 50, 'notnull' => false]);
                $t->addColumn('timestamp', 'datetime', ['notnull' => false]);
                $t->addColumn('level', 'string', ['length' => 16, 'notnull' => false]);
                $t->addColumn('message', 'text', ['notnull' => false]);
                $t->setPrimaryKey(['idlogger_message']);
            },

            // The core selfwatch table: a single ingested log record, belonging to a project
            // (idsite). `context` holds arbitrary structured fields as a JSON object.
            'log_entry' => function (DbalTable $t): void {
                $t->addColumn('idlogentry', 'bigint', ['autoincrement' => true]);
                $t->addColumn('idsite', 'integer');
                $t->addColumn('source', 'string', ['length' => 120, 'default' => '']);
                $t->addColumn('level', 'string', ['length' => 16, 'default' => 'info']);
                $t->addColumn('message', 'text');
                $t->addColumn('context', 'text', ['notnull' => false]);
                $t->addColumn('trace_id', 'string', ['length' => 64, 'notnull' => false]);
                $t->addColumn('host', 'string', ['length' => 190, 'notnull' => false]);
                // Sentry-style event attributes (kind=log for plain logs, event for SDK events).
                $t->addColumn('kind', 'string', ['length' => 10, 'default' => 'log']);
                $t->addColumn('event_id', 'string', ['length' => 32, 'notnull' => false]);
                $t->addColumn('platform', 'string', ['length' => 64, 'notnull' => false]);
                $t->addColumn('environment', 'string', ['length' => 64, 'notnull' => false]);
                $t->addColumn('app_release', 'string', ['length' => 190, 'notnull' => false]);
                $t->addColumn('logged_at', 'datetime');
                $t->addColumn('received_at', 'datetime');
                $t->setPrimaryKey(['idlogentry']);
                $t->addIndex(['idsite', 'logged_at']);
                $t->addIndex(['idsite', 'level', 'logged_at']);
                $t->addIndex(['idsite', 'source', 'logged_at']);
                $t->addIndex(['idsite', 'environment', 'logged_at']);
                $t->addIndex(['trace_id']);
                $t->addIndex(['event_id']);
            },

            // An alert rule: when an ingested log matches (min severity / source / message),
            // fire a notification through a channel, throttled by a per-rule cooldown.
            'alert_rule' => function (DbalTable $t): void {
                $t->addColumn('idalertrule', 'bigint', ['autoincrement' => true]);
                $t->addColumn('idsite', 'integer');
                $t->addColumn('name', 'string', ['length' => 190]);
                $t->addColumn('enabled', 'smallint', ['default' => 1]);
                $t->addColumn('min_level', 'string', ['length' => 16, 'default' => 'error']);
                $t->addColumn('source', 'string', ['length' => 120, 'default' => '']);
                $t->addColumn('message_contains', 'string', ['length' => 255, 'default' => '']);
                $t->addColumn('channel', 'string', ['length' => 20, 'default' => 'webhook']);
                $t->addColumn('channel_target', 'string', ['length' => 500, 'default' => '']);
                $t->addColumn('cooldown_seconds', 'integer', ['default' => 0]);
                $t->addColumn('last_fired_at', 'datetime', ['notnull' => false]);
                $t->addColumn('created_at', 'datetime');
                $t->setPrimaryKey(['idalertrule']);
                $t->addIndex(['idsite', 'enabled']);
            },

            // A record of every fired alert (for the activity log / debugging delivery).
            'alert_event' => function (DbalTable $t): void {
                $t->addColumn('idalertevent', 'bigint', ['autoincrement' => true]);
                $t->addColumn('idalertrule', 'integer');
                $t->addColumn('idsite', 'integer');
                $t->addColumn('idlogentry', 'bigint', ['notnull' => false]);
                $t->addColumn('level', 'string', ['length' => 16]);
                $t->addColumn('message', 'text');
                $t->addColumn('channel', 'string', ['length' => 20]);
                $t->addColumn('target', 'string', ['length' => 500]);
                $t->addColumn('status', 'string', ['length' => 20]);
                $t->addColumn('error', 'text', ['notnull' => false]);
                $t->addColumn('fired_at', 'datetime');
                $t->setPrimaryKey(['idalertevent']);
                $t->addIndex(['idsite', 'fired_at']);
                $t->addIndex(['idalertrule', 'fired_at']);
            },
        ];
    }

    /**
     * Get the SQL to create the framework tables, keyed by table name (without prefix).
     * Each value is the DBAL-generated CREATE TABLE plus any CREATE INDEX statements,
     * joined with ";\n".
     *
     * @return array<string,string>
     */
    public function getTablesCreateSql()
    {
        $platform = $this->getDbalPlatform();
        $prefix   = $this->getTablePrefix();

        $result = [];
        foreach ($this->tableDefinitions() as $shortName => $definer) {
            $schema = new DbalSchema();
            $table  = $schema->createTable($prefix . $shortName);
            $definer($table);
            $result[$shortName] = implode(";\n", $schema->toSql($platform));
        }

        return $result;
    }

    /**
     * Get the SQL to create a specific table.
     *
     * @param string $tableName
     * @throws Exception
     * @return string  SQL
     */
    public function getTableCreateSql($tableName)
    {
        $tables = $this->getTablesCreateSql();

        if (!isset($tables[$tableName])) {
            throw new Exception("The table '$tableName' SQL creation code couldn't be found.");
        }

        return $tables[$tableName];
    }

    /**
     * Names of all the prefixed tables. Doesn't use the DB.
     *
     * @return array  Table names
     */
    public function getTablesNames()
    {
        $aTables      = array_keys($this->getTablesCreateSql());
        $prefixTables = $this->getTablePrefix();

        $return = array();
        foreach ($aTables as $table) {
            $return[] = $prefixTables . $table;
        }

        return $return;
    }
}