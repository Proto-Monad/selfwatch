<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/selfwatch/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Dbal;

use Doctrine\DBAL\Connection as DbalConnection;
use Exception;
use Piwik\Db;

/**
 * Accessor for a Doctrine DBAL connection that shares Matomo's existing PDO handle.
 *
 * selfwatch uses DBAL's query builder for its own data access and for the framework
 * queries it has ported off raw MySQL SQL, while the surrounding (Zend_Db based) code keeps
 * working unchanged - both speak to the same underlying PDO connection.
 *
 * Both SQLite (the development/default engine) and MySQL/MariaDB are wired up: the PDO
 * driver name picks an AbstractSQLiteDriver- or AbstractMySQLDriver-based wrapper for the
 * same PDO handle.
 */
class Connection
{
    /** @var DbalConnection|null */
    private static $connection = null;

    /**
     * Returns a DBAL connection wrapping the current Matomo PDO handle.
     *
     * @throws Exception if the underlying adapter does not expose a PDO connection.
     */
    public static function get(): DbalConnection
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $pdo = Db::get()->getConnection();

        if (!$pdo instanceof \PDO) {
            throw new Exception('selfwatch DBAL layer requires a PDO-based database adapter.');
        }

        $driverName = strtolower((string) $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));

        switch ($driverName) {
            case 'sqlite':
                $driver = new PdoSqliteDriver($pdo);
                break;
            case 'mysql':
                $driver = new PdoMySQLDriver($pdo);
                break;
            default:
                throw new Exception(sprintf(
                    "selfwatch DBAL layer does not yet support the '%s' PDO driver.",
                    $driverName
                ));
        }

        self::$connection = new DbalConnection([], $driver);

        return self::$connection;
    }

    /**
     * Drops the cached connection (e.g. after the underlying Matomo connection is reset).
     */
    public static function reset(): void
    {
        self::$connection = null;
    }
}
