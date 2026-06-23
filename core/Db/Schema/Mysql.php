<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Schema;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Exception;
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db\SchemaInterface;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Version;

/**
 * MySQL schema
 */
class Mysql implements SchemaInterface
{
    use SelfwatchDbalSchemaTrait;

    public const OPTION_NAME_MATOMO_INSTALL_VERSION = 'install_version';
    public const MAX_TABLE_NAME_LENGTH = 64;
    private $tablesInstalled = null;
    protected $minimumSupportedVersion = '5.5';

    public function getDatabaseType(): string
    {
        return 'MySQL';
    }

    public function getMinimumSupportedVersion(): string
    {
        return $this->minimumSupportedVersion;
    }

    /**
     * Get list of installed columns in a table
     *
     * @param  string $tableName The name of a table.
     *
     * @return array  Installed columns indexed by the column name.
     */
    public function getTableColumns($tableName)
    {
        $db = $this->getDb();

        $allColumns = $db->fetchAll("SHOW COLUMNS FROM `$tableName`");

        $fields = array();
        foreach ($allColumns as $column) {
            $fields[trim($column['Field'])] = $column;
        }

        return $fields;
    }

    /**
     * Get list of tables installed (including tables defined by deactivated plugins)
     *
     * @param bool $forceReload Invalidate cache
     * @return array  installed Tables
     */
    public function getTablesInstalled($forceReload = true)
    {
        if (
            is_null($this->tablesInstalled)
            || $forceReload === true
        ) {
            $db = $this->getDb();
            $prefixTables = $this->getTablePrefixEscaped();

            $allTables = $this->getAllExistingTables($prefixTables);

            // all the tables to be installed
            $allMyTables = $this->getTablesNames();

            /**
             * Triggered when detecting which tables have already been created by Matomo.
             * This should be used by plugins to define it's database tables. Table names need to be added prefixed.
             *
             * **Example**
             *
             *     Piwik::addAction('Db.getTablesInstalled', function(&$allTablesInstalled) {
             *         $allTablesInstalled = 'log_custom';
             *     });
             * @param array $result
             */
            if (count($allTables) && empty($GLOBALS['DISABLE_GET_TABLES_INSTALLED_EVENTS_FOR_TEST'])) {
                Manager::getInstance()->loadPlugins(Manager::getAllPluginsNames());
                Piwik::postEvent('Db.getTablesInstalled', [&$allMyTables]);
                Manager::getInstance()->unloadPlugins();
                Manager::getInstance()->loadActivatedPlugins();
            }

            // we get the intersection between all the tables in the DB and the tables to be installed
            $tablesInstalled = array_intersect($allMyTables, $allTables);

            // at this point we have the static list of core tables, but let's add the monthly archive tables
            $allArchiveNumeric = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_numeric%'");
            $allArchiveBlob    = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_blob%'");

            $allTablesReallyInstalled = array_merge($tablesInstalled, $allArchiveNumeric, $allArchiveBlob);

            $allTablesReallyInstalled = array_unique($allTablesReallyInstalled);

            $this->tablesInstalled = $allTablesReallyInstalled;
        }

        return $this->tablesInstalled;
    }

    /**
     * Checks whether any table exists
     *
     * @return bool  True if tables exist; false otherwise
     */
    public function hasTables()
    {
        return count($this->getTablesInstalled()) != 0;
    }

    /**
     * Create database
     *
     * @param string $dbName Name of the database to create
     */
    public function createDatabase($dbName = null)
    {
        if (is_null($dbName)) {
            $dbName = $this->getDbName();
        }

        $createOptions = $this->getDatabaseCreateOptions();
        $dbName = str_replace('`', '', $dbName);

        Db::exec("CREATE DATABASE IF NOT EXISTS `$dbName` $createOptions");
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $nameWithoutPrefix The name of the table without any piwik prefix.
     * @param string $createDefinition  The table create definition, see the "MySQL CREATE TABLE" specification for
     *                                  more information.
     * @throws \Exception
     */
    public function createTable($nameWithoutPrefix, $createDefinition)
    {
        $statement = sprintf(
            "CREATE TABLE IF NOT EXISTS `%s` ( %s ) %s;",
            Common::prefixTable($nameWithoutPrefix),
            $createDefinition,
            $this->getTableCreateOptions()
        );

        try {
            Db::exec($statement);
        } catch (Exception $e) {
            // mysql code error 1050:table already exists
            // see bug #153 https://github.com/piwik/piwik/issues/153
            if (!$this->getDb()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    /**
     * Drop database
     */
    public function dropDatabase($dbName = null)
    {
        $dbName = $dbName ?: $this->getDbName();
        $dbName = str_replace('`', '', $dbName);
        Db::exec("DROP DATABASE IF EXISTS `" . $dbName . "`");
    }

    /**
     * Create all tables
     */
    public function createTables()
    {
        $db = $this->getDb();
        $prefixTables = $this->getTablePrefix();

        $tablesAlreadyInstalled = $this->getAllExistingTables($prefixTables);
        $tablesToCreate = $this->getTablesCreateSql();
        unset($tablesToCreate['archive_blob']);
        unset($tablesToCreate['archive_numeric']);

        foreach ($tablesToCreate as $tableName => $tableSql) {
            $tableName = $prefixTables . $tableName;
            if (!in_array($tableName, $tablesAlreadyInstalled)) {
                $db->query($tableSql);
            }
        }
    }

    /**
     * Creates an entry in the User table for the "anonymous" user.
     */
    public function createAnonymousUser()
    {
        $now = Date::factory('now')->getDatetime();
        // The anonymous user is the user that is assigned by default
        // note that the token_auth value is anonymous, which is assigned by default as well in the Login plugin
        $db = $this->getDb();
        $db->query("INSERT IGNORE INTO " . Common::prefixTable("user") . "
                    (`login`, `password`, `email`, `twofactor_secret`, `superuser_access`, `date_registered`, `ts_password_modified`,
                    `idchange_last_viewed`)
                    VALUES ( 'anonymous', '', 'anonymous@example.org', '', 0, '$now', '$now' , NULL);");

        $model = new Model();
        $model->addTokenAuth('anonymous', 'anonymous', 'anonymous default token', $now);
    }

    /**
     * Records the Matomo version a user used when installing this Matomo for the first time
     */
    public function recordInstallVersion()
    {
        if (!self::getInstallVersion()) {
            Option::set(self::OPTION_NAME_MATOMO_INSTALL_VERSION, Version::VERSION);
        }
    }

    /**
     * Returns which Matomo version was used to install this Matomo for the first time.
     */
    public function getInstallVersion()
    {
        Option::clearCachedOption(self::OPTION_NAME_MATOMO_INSTALL_VERSION);
        $version = Option::get(self::OPTION_NAME_MATOMO_INSTALL_VERSION);
        if (!empty($version)) {
            return $version;
        }
    }

    /**
     * Truncate all tables
     */
    public function truncateAllTables()
    {
        $tables = $this->getAllExistingTables();
        foreach ($tables as $table) {
            Db::query("TRUNCATE `$table`");
        }
    }

    /**
     * Adds a MAX_EXECUTION_TIME hint into a SELECT query if $limit is bigger than 0
     *
     * @param string $sql  query to add hint to
     * @param float $limit  time limit in seconds
     */
    public function addMaxExecutionTimeHintToQuery(string $sql, float $limit): string
    {
        if ($limit <= 0) {
            return $sql;
        }

        $timeInMs = $limit * 1000;
        $timeInMs = (int) $timeInMs;

        return DbHelper::addOptimizerHintToQuery($sql, 'MAX_EXECUTION_TIME(' . $timeInMs . ')');
    }

    public function supportsComplexColumnUpdates(): bool
    {
        return true;
    }

    /**
     * Returns the default collation for a charset.
     *
     * Will return an empty string for an unknown charset
     * (can happen for alias charsets like "utf8").
     *
     * @throws Exception
     */
    public function getDefaultCollationForCharset(string $charset): string
    {
        $result = $this->getDb()->fetchRow('SHOW CHARACTER SET WHERE `Charset` = ?', [$charset]);

        return $result['Default collation'] ?? '';
    }

    public function getDefaultPort(): int
    {
        return 3306;
    }

    public function getTableCreateOptions(): string
    {
        $engine = $this->getTableEngine();
        $charset = $this->getUsedCharset();
        $collation = $this->getUsedCollation();
        $rowFormat = $this->getTableRowFormat();

        $options = "ENGINE=$engine DEFAULT CHARSET=$charset";

        if ('' !== $collation) {
            $options .= " COLLATE=$collation";
        }

        if ('' !== $rowFormat) {
            $options .= " $rowFormat";
        }

        return $options;
    }

    public function optimizeTables(array $tables, bool $force = false): bool
    {
        $optimize = Config::getInstance()->General['enable_sql_optimize_queries'];

        if (
            empty($optimize)
            && !$force
        ) {
            return false;
        }

        if (empty($tables)) {
            return false;
        }

        if (
            !$this->isOptimizeInnoDBSupported()
            && !$force
        ) {
            // filter out all InnoDB tables
            $myisamDbTables = array();
            foreach ($this->getTableStatus() as $row) {
                if (
                    strtolower($row['Engine']) == 'myisam'
                    && in_array($row['Name'], $tables)
                ) {
                    $myisamDbTables[] = $row['Name'];
                }
            }

            $tables = $myisamDbTables;
        }

        if (empty($tables)) {
            return false;
        }

        // optimize the tables
        $success = true;
        foreach ($tables as &$t) {
            $ok = Db::query('OPTIMIZE TABLE ' . $t);
            if (!$ok) {
                $success = false;
            }
        }

        return $success;
    }

    public function isOptimizeInnoDBSupported(): bool
    {
        $version = strtolower($this->getVersion());

        // Note: This check for MariaDb is here on purpose, so it's working correctly for people
        // having MySQL still configured, when using MariaDb
        if (strpos($version, "mariadb") === false) {
            return false;
        }

        $semanticVersion = strstr($version, '-', $beforeNeedle = true);
        return version_compare($semanticVersion, '10.1.1', '>=');
    }

    public function supportsRankingRollupWithoutExtraSorting(): bool
    {
        return true;
    }

    public function supportsSortingInSubquery(): bool
    {
        return true;
    }

    public function getSupportedReadIsolationTransactionLevel(): string
    {
        return 'READ UNCOMMITTED';
    }

    public function hasReachedEOL(): bool
    {
        $currentVersion = $this->getVersion();

        // Aurora is managed by AWS and is updated automatically if EOL, therefor we can ignore that here
        $auroraQuery = $this->getDb()->query('SHOW VARIABLES LIKE "aurora%"');

        if ($this->getDb()->rowCount($auroraQuery) > 0) {
            return false;
        }

        // End of security update for certain MySQL versions as of https://en.wikipedia.org/wiki/MySQL#Release_history

        // Support for 8.0 LTS ends in April 2026
        if (
            version_compare($currentVersion, '8.0', '>=') &&
            version_compare($currentVersion, '8.1', '<') &&
            Date::today()->isEarlier(Date::factory('2026-05-01'))
        ) {
            return false;
        }

        // Support for 8.4 LTS ends in April 2032
        if (
            version_compare($currentVersion, '8.4', '>=') &&
            version_compare($currentVersion, '8.5', '<') &&
            Date::today()->isEarlier(Date::factory('2032-05-01'))
        ) {
            return false;
        }

        // Support for all other versions prior to 9.3 (not covered by conditions above) already ended
        if (version_compare($currentVersion, '9.3', '<')) {
            return true;
        }

        return false;
    }

    protected function getDatabaseCreateOptions(): string
    {
        $charset = DbHelper::getDefaultCharset();
        $collation = $this->getDefaultCollationForCharset($charset);

        $options = "DEFAULT CHARACTER SET $charset";

        if ('' !== $collation) {
            $options .= " COLLATE $collation";
        }

        return $options;
    }

    protected function getTableEngine()
    {
        return $this->getDbSettings()->getEngine();
    }

    protected function getTableRowFormat(): string
    {
        return $this->getDbSettings()->getRowFormat();
    }

    protected function getUsedCharset(): string
    {
        return $this->getDbSettings()->getUsedCharset();
    }

    protected function getUsedCollation(): string
    {
        return $this->getDbSettings()->getUsedCollation();
    }

    private function getTablePrefix()
    {
        return $this->getDbSettings()->getTablePrefix();
    }

    protected function getDbalPlatform(): AbstractPlatform
    {
        return new MySQLPlatform();
    }

    public function getVersion(): string
    {
        return Db::fetchOne("SELECT VERSION()");
    }

    protected function getTableStatus()
    {
        return Db::fetchAll("SHOW TABLE STATUS");
    }

    private function getDb()
    {
        return Db::get();
    }

    private function getDbSettings()
    {
        return new Db\Settings();
    }

    private function getDbName()
    {
        return $this->getDbSettings()->getDbName();
    }

    private function getAllExistingTables($prefixTables = false)
    {
        if (empty($prefixTables)) {
            $prefixTables = $this->getTablePrefixEscaped();
        }

        return Db::get()->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "%'");
    }

    private function getTablePrefixEscaped()
    {
        $prefixTables = $this->getTablePrefix();
        // '_' matches any character; force it to be literal
        $prefixTables = str_replace('_', '\_', $prefixTables);
        return $prefixTables;
    }
}
