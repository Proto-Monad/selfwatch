<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/selfwatch/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Adapter\Pdo;

use Exception;
use PDO;
use Piwik\Db\AdapterInterface;
use Piwik\Db\Schema;
use Piwik\Piwik;
use Zend_Db_Adapter_Pdo_Sqlite;

/**
 * selfwatch SQLite adapter.
 *
 * Wraps the bundled {@see Zend_Db_Adapter_Pdo_Sqlite} so SQLite can be selected at
 * install time exactly like MySQL/MariaDB. SQLite is the recommended engine for
 * development and small single-node deployments; it has no concept of a database
 * "server", so the connection/version helpers are simplified accordingly.
 */
class Sqlite extends Zend_Db_Adapter_Pdo_Sqlite implements AdapterInterface
{
    /**
     * @param array $config database configuration
     */
    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Returns the PDO connection handle, opening it on first use.
     *
     * @return PDO
     */
    public function getConnection()
    {
        if ($this->_connection) {
            return $this->_connection;
        }

        $this->_connect();

        return $this->_connection;
    }

    /**
     * Reset the configuration variables in this adapter.
     */
    public function resetConfig()
    {
        $this->_config = array();
    }

    /**
     * Return default port. SQLite is file-based and has no port; 0 signals "n/a".
     *
     * @deprecated Use Schema::getDefaultPortForSchema instead
     * @return int
     */
    public static function getDefaultPort()
    {
        return 0;
    }

    /**
     * Check SQLite library version against the minimum supported by the schema.
     *
     * @throws Exception
     */
    public function checkServerVersion()
    {
        $requiredVersion = Schema::getInstance()->getMinimumSupportedVersion();
        $serverVersion   = $this->getServerVersion();

        if (!empty($serverVersion) && version_compare($serverVersion, $requiredVersion) === -1) {
            throw new Exception(Piwik::translate('General_ExceptionDatabaseVersion', array('SQLite', $serverVersion, $requiredVersion)));
        }
    }

    /**
     * The PDO client and SQLite library versions are the same thing here.
     *
     * @throws Exception
     */
    public function checkClientVersion()
    {
        // Nothing to verify: SQLite is embedded, so client and server versions match.
    }

    /**
     * Returns true if this adapter's required extensions are enabled.
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return extension_loaded('PDO')
            && extension_loaded('pdo_sqlite')
            && in_array('sqlite', PDO::getAvailableDrivers());
    }

    /**
     * SQLite stores BLOBs natively.
     *
     * @return bool
     */
    public function hasBlobDataType()
    {
        return true;
    }

    /**
     * SQLite has no bulk LOAD DATA INFILE equivalent; bulk inserts fall back to
     * multi-row INSERT statements handled at a higher level.
     *
     * @return bool
     */
    public function hasBulkLoader()
    {
        return false;
    }

    /**
     * Best-effort test of a SQLite error against a (MySQL-style) error number.
     *
     * The inherited codebase checks for specific MySQL error numbers (e.g. 1050
     * "table exists", 1062 "duplicate key", 1146 "table missing"). SQLite reports
     * different numbers, so we translate the handful that matter by inspecting the
     * SQLSTATE/driver code and message text.
     *
     * @param Exception $e
     * @param string|int $errno
     * @return bool
     */
    public function isErrNo($e, $errno)
    {
        $message = $e->getMessage();
        $errno   = (string) $errno;

        // Map of MySQL error numbers -> substrings that identify the equivalent
        // SQLite error in the PDOException message.
        $map = array(
            '1050' => 'already exists',      // table/index already exists
            '1051' => 'no such table',       // unknown table
            '1146' => 'no such table',       // table doesn't exist
            '1054' => 'no such column',      // unknown column
            '1062' => 'UNIQUE constraint',   // duplicate entry for key
            '1060' => 'duplicate column',    // duplicate column name
            '1091' => 'no such',             // can't drop; doesn't exist
        );

        if (isset($map[$errno]) && stripos($message, $map[$errno]) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Return number of affected rows in the last query.
     *
     * @param mixed $queryResult Result from query()
     * @return int
     */
    public function rowCount($queryResult)
    {
        return $queryResult->rowCount();
    }

    /**
     * Is the connection character set UTF-8? SQLite text is always UTF-8.
     *
     * @return bool
     */
    public function isConnectionUTF8()
    {
        return true;
    }

    // -- TransactionalDatabaseInterface ------------------------------------------------
    //
    // SQLite uses a single, database-wide isolation model (serializable) and has no
    // per-session isolation level to query or set, so these are simple constants/no-ops.

    private $supportsTransactionLevelForNonLockingReads;

    public function getCurrentTransactionIsolationLevelForSession(): string
    {
        return 'SERIALIZABLE';
    }

    public function setTransactionIsolationLevel(string $level): void
    {
        // No-op: SQLite does not support per-session transaction isolation levels.
    }

    public function getSupportsTransactionLevelForNonLockingReads(): ?bool
    {
        return $this->supportsTransactionLevelForNonLockingReads;
    }

    public function setSupportsTransactionLevelForNonLockingReads(?bool $supports = null): void
    {
        $this->supportsTransactionLevelForNonLockingReads = $supports;
    }
}
