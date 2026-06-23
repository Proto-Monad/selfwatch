<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * SQLite adapter for the bundled Zend_Db layer. selfwatch is a fork of Matomo;
 * the upstream Zend_Db library only shipped MySQL/MariaDB adapters, so this file
 * provides the low-level PDO SQLite driver that the rest of the data layer builds on.
 *
 * @link    https://github.com/selfwatch/selfwatch
 * @license http://framework.zend.com/license/new-bsd  New BSD License (Zend portions)
 */

/**
 * Class for connecting to SQLite databases and performing common operations.
 */
class Zend_Db_Adapter_Pdo_Sqlite extends Zend_Db_Adapter_Pdo_Abstract
{
    /**
     * PDO type.
     *
     * @var string
     */
    protected $_pdoType = 'sqlite';

    /**
     * Keys are UPPERCASE SQL datatypes or the constants
     * Zend_Db::INT_TYPE, Zend_Db::BIGINT_TYPE, or Zend_Db::FLOAT_TYPE.
     *
     * SQLite is dynamically typed, but Matomo/selfwatch declares columns using
     * MySQL-flavoured type names (INTEGER, BIGINT, ...) which we honour here so that
     * Zend_Db keeps treating the relevant columns as numeric.
     *
     * @var array Associative array of datatypes to values 0, 1, or 2.
     */
    protected $_numericDataTypes = array(
        Zend_Db::INT_TYPE    => Zend_Db::INT_TYPE,
        Zend_Db::BIGINT_TYPE => Zend_Db::BIGINT_TYPE,
        Zend_Db::FLOAT_TYPE  => Zend_Db::FLOAT_TYPE,
        'INT'                => Zend_Db::INT_TYPE,
        'INTEGER'            => Zend_Db::INT_TYPE,
        'MEDIUMINT'          => Zend_Db::INT_TYPE,
        'SMALLINT'           => Zend_Db::INT_TYPE,
        'TINYINT'            => Zend_Db::INT_TYPE,
        'BIGINT'             => Zend_Db::BIGINT_TYPE,
        'SERIAL'             => Zend_Db::BIGINT_TYPE,
        'DEC'                => Zend_Db::FLOAT_TYPE,
        'DECIMAL'            => Zend_Db::FLOAT_TYPE,
        'DOUBLE'             => Zend_Db::FLOAT_TYPE,
        'DOUBLE PRECISION'   => Zend_Db::FLOAT_TYPE,
        'FIXED'              => Zend_Db::FLOAT_TYPE,
        'FLOAT'              => Zend_Db::FLOAT_TYPE,
        'REAL'               => Zend_Db::FLOAT_TYPE,
        'NUMERIC'            => Zend_Db::FLOAT_TYPE,
    );

    /**
     * SQLite is file-based and needs no login credentials, so only the database path is
     * required (the inherited check also demands username/password).
     *
     * @param array $config
     * @throws Zend_Db_Adapter_Exception
     */
    protected function _checkRequiredOptions(array $config)
    {
        if (!array_key_exists('dbname', $config)) {
            throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'dbname' that names the database instance");
        }
    }

    /**
     * Build the SQLite DSN. Unlike the server-based engines, SQLite takes a single
     * filesystem path (or ":memory:") and ignores host/port/user/password.
     *
     * @return string
     */
    protected function _dsn()
    {
        $dbPath = $this->_config['dbname'] ?? '';

        if ($dbPath === '' || strtolower($dbPath) === ':memory:') {
            return 'sqlite::memory:';
        }

        return 'sqlite:' . $dbPath;
    }

    /**
     * Creates a PDO object and connects to the database, then applies the pragmas
     * that make SQLite behave acceptably under a web workload.
     *
     * @return void
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }

        parent::_connect();

        // Enforce referential integrity, wait on locks instead of failing immediately,
        // and use a write-ahead log so reads (the log viewer) don't block ingestion writes.
        $this->_connection->exec('PRAGMA foreign_keys = ON');
        $this->_connection->exec('PRAGMA busy_timeout = 5000');
        $this->_connection->exec('PRAGMA journal_mode = WAL');
        $this->_connection->exec('PRAGMA synchronous = NORMAL');
    }

    /**
     * SQLite accepts back-ticked identifiers for MySQL compatibility, which keeps the
     * large body of inherited raw SQL working unchanged.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return '`';
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        return $this->fetchCol(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
        );
    }

    /**
     * Returns the column descriptions for a table, in the associative format that the
     * rest of Zend_Db (and Matomo's Db helpers) expect. Derived from PRAGMA table_info.
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL (unused for SQLite)
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        $sql  = 'PRAGMA table_info(' . $this->quoteIdentifier($tableName, true) . ')';
        $stmt = $this->query($sql);
        $result = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);

        $desc = array();
        $i = 1;
        $p = 1;

        foreach ($result as $row) {
            $length    = null;
            $scale     = null;
            $precision = null;
            $primary   = false;
            $primaryPosition = null;
            $identity  = false;

            $type = strtoupper((string) $row['type']);

            if (preg_match('/^((?:VAR)?CHAR)\s*\((\d+)\)/i', $type, $matches)) {
                $type   = strtoupper($matches[1]);
                $length = (int) $matches[2];
            } elseif (preg_match('/^(DECIMAL|NUMERIC)\s*\((\d+),\s*(\d+)\)/i', $type, $matches)) {
                $type      = strtoupper($matches[1]);
                $precision = (int) $matches[2];
                $scale     = (int) $matches[3];
            } elseif (preg_match('/^([A-Z ]+?)\s*\(\d+\)/i', $type, $matches)) {
                $type = trim(strtoupper($matches[1]));
            }

            if ((int) $row['pk'] > 0) {
                $primary         = true;
                $primaryPosition = $p;
                // In SQLite an INTEGER PRIMARY KEY column is an alias for the implicit
                // ROWID and therefore auto-increments, which is the IDENTITY column.
                $identity = (strpos($type, 'INT') !== false);
                ++$p;
            }

            $name = $this->foldCase($row['name']);

            $desc[$name] = array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $this->foldCase($tableName),
                'COLUMN_NAME'      => $name,
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'        => strtolower($type),
                'DEFAULT'          => $row['dflt_value'],
                'NULLABLE'         => !((int) $row['notnull']),
                'LENGTH'           => $length,
                'SCALE'            => $scale,
                'PRECISION'        => $precision,
                'UNSIGNED'         => false,
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity,
            );
            ++$i;
        }

        return $desc;
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param  string  $sql
     * @param  integer $count
     * @param  integer $offset OPTIONAL
     * @throws Zend_Db_Adapter_Exception
     * @return string
     */
    public function limit($sql, $count, $offset = 0)
    {
        $count = intval($count);
        if ($count <= 0) {
            throw new Zend_Db_Adapter_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new Zend_Db_Adapter_Exception("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $count";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }
}
