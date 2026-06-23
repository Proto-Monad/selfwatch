<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Dbal;

use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\PDO\Connection as PdoConnection;
use PDO;
use SensitiveParameter;

/**
 * A Doctrine DBAL driver that reuses an already-open PDO/SQLite handle.
 *
 * selfwatch keeps Matomo's existing (Zend_Db) connection as the single owner of the PDO
 * resource; this driver lets DBAL's query builder share that exact handle instead of
 * opening a second connection (which SQLite's single-writer model would fight over).
 */
final class PdoSqliteDriver extends AbstractSQLiteDriver
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function connect(
        #[SensitiveParameter]
        array $params,
    ): DriverConnection {
        return new PdoConnection($this->pdo);
    }
}
