<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db\Dbal\Connection;
use Piwik\Option;

/**
 * Data layer for selfwatch log entries.
 *
 * All access goes through Doctrine DBAL's query builder (shared with the rest of the app),
 * so it is portable across SQLite/MySQL/Postgres and contains no hand-written, engine-
 * specific SQL.
 */
class Model
{
    public const LEVELS = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    public const MAX_MESSAGE_LEN = 65535;
    public const MAX_CONTEXT_LEN = 131072;

    private const TOKEN_OPTION_PREFIX = 'Logs_ingest_token_';

    private function table(): string
    {
        return Common::prefixTable('log_entry');
    }

    // -- Ingestion ----------------------------------------------------------------------

    /**
     * Insert a batch of normalised log entries. Each entry is an associative array with the
     * columns of the log_entry table.
     *
     * @param array<int,array<string,mixed>> $entries
     * @return array<int,array<string,mixed>> the stored rows (normalised, each with an
     *         `idlogentry`); the count of these is the number written.
     */
    public function insertBatch(int $idSite, array $entries): array
    {
        if (empty($entries)) {
            return [];
        }

        $conn        = Connection::get();
        $table       = $this->table();
        $receivedAt  = Date::now()->getDatetime();
        $stored      = [];

        $conn->transactional(function ($conn) use ($entries, $idSite, $table, $receivedAt, &$stored) {
            foreach ($entries as $entry) {
                $row = [
                    'idsite'      => $idSite,
                    'source'      => (string) ($entry['source'] ?? ''),
                    'level'       => $this->normaliseLevel($entry['level'] ?? 'info'),
                    'message'     => substr((string) ($entry['message'] ?? ''), 0, self::MAX_MESSAGE_LEN),
                    'context'     => isset($entry['context']) && $entry['context'] !== null
                        ? substr(is_string($entry['context']) ? $entry['context'] : (string) json_encode($entry['context']), 0, self::MAX_CONTEXT_LEN)
                        : null,
                    'trace_id'    => isset($entry['trace_id']) ? substr((string) $entry['trace_id'], 0, 64) : null,
                    'host'        => isset($entry['host']) ? substr((string) $entry['host'], 0, 190) : null,
                    // Sentry-event attributes (null/'log' for plain logs).
                    'kind'        => (string) ($entry['kind'] ?? 'log'),
                    'event_id'    => isset($entry['event_id']) ? substr((string) $entry['event_id'], 0, 32) : null,
                    'platform'    => isset($entry['platform']) ? substr((string) $entry['platform'], 0, 64) : null,
                    'environment' => isset($entry['environment']) ? substr((string) $entry['environment'], 0, 64) : null,
                    'app_release' => isset($entry['app_release']) ? substr((string) $entry['app_release'], 0, 190) : null,
                    'logged_at'   => $this->normaliseTimestamp($entry['timestamp'] ?? null),
                    'received_at' => $receivedAt,
                ];
                $conn->insert($table, $row);
                $row['idlogentry'] = (int) $conn->lastInsertId();
                $stored[] = $row;
            }
        });

        return $stored;
    }

    /**
     * Severity rank of a level (higher = more severe), used by alert evaluation.
     */
    public function levelRank(string $level): int
    {
        $rank = array_search($this->normaliseLevel($level), self::LEVELS, true);
        return $rank === false ? 1 : (int) $rank;
    }

    public function normaliseLevel($level): string
    {
        $level = strtolower(trim((string) $level));
        if ($level === 'warn') {
            $level = 'warning';
        }
        if ($level === 'err' || $level === 'fatal') {
            $level = $level === 'fatal' ? 'critical' : 'error';
        }
        return in_array($level, self::LEVELS, true) ? $level : 'info';
    }

    private function normaliseTimestamp($timestamp): string
    {
        if (empty($timestamp)) {
            return Date::now()->getDatetime();
        }
        try {
            // Accept unix timestamps (int/float) and ISO-8601 / common datetime strings.
            if (is_numeric($timestamp)) {
                return Date::factory((int) $timestamp)->getDatetime();
            }
            return Date::factory((string) $timestamp)->getDatetime();
        } catch (\Exception $e) {
            return Date::now()->getDatetime();
        }
    }

    // -- Querying (used by the Logs viewer) ---------------------------------------------

    /**
     * Query log entries with optional filters, newest first.
     *
     * @param array<string,mixed> $filters keys: level, source, q (message search),
     *                                      trace_id, from, to, after_id (for live tail)
     * @return array<int,array<string,mixed>>
     */
    public function getLogs(int $idSite, array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $qb = Connection::get()->createQueryBuilder();
        $qb->select('*')->from($this->table())->where('idsite = :idsite')->setParameter('idsite', $idSite);

        $this->applyFilters($qb, $filters);

        if (!empty($filters['after_id'])) {
            // Live tail / "newer" page: only entries newer than a cursor, oldest-first.
            $qb->andWhere('idlogentry > :after_id')->setParameter('after_id', (int) $filters['after_id'], \Doctrine\DBAL\ParameterType::INTEGER);
            $qb->orderBy('idlogentry', 'ASC');
        } else {
            // Keyset pagination: "older" page sits before a cursor. No OFFSET, so it stays fast
            // on millions of rows (idlogentry is the PK and is indexed).
            if (!empty($filters['before_id'])) {
                $qb->andWhere('idlogentry < :before_id')->setParameter('before_id', (int) $filters['before_id'], \Doctrine\DBAL\ParameterType::INTEGER);
            }
            $qb->orderBy('idlogentry', 'DESC');
        }

        $qb->setMaxResults($limit);
        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function countLogs(int $idSite, array $filters = []): int
    {
        $qb = Connection::get()->createQueryBuilder();
        $qb->select('COUNT(*)')->from($this->table())->where('idsite = :idsite')->setParameter('idsite', $idSite);
        $this->applyFilters($qb, $filters);

        return (int) $qb->executeQuery()->fetchOne();
    }

    /**
     * @return array<string,int> level => count, for the current filter window
     */
    public function getLevelCounts(int $idSite, array $filters = []): array
    {
        $qb = Connection::get()->createQueryBuilder();
        $qb->select('level', 'COUNT(*) AS cnt')->from($this->table())
            ->where('idsite = :idsite')->setParameter('idsite', $idSite)
            ->groupBy('level');
        // Apply everything except the level filter itself, so the counts show all levels.
        $this->applyFilters($qb, array_diff_key($filters, ['level' => true]));

        $counts = [];
        foreach ($qb->executeQuery()->fetchAllAssociative() as $row) {
            $counts[$row['level']] = (int) $row['cnt'];
        }
        return $counts;
    }

    /**
     * @return string[] distinct sources seen for the project
     */
    public function getSources(int $idSite): array
    {
        return $this->distinctValues($idSite, 'source');
    }

    /** @return string[] distinct environments seen for the project */
    public function getEnvironments(int $idSite): array
    {
        return $this->distinctValues($idSite, 'environment');
    }

    /** @return string[] distinct platforms seen for the project */
    public function getPlatforms(int $idSite): array
    {
        return $this->distinctValues($idSite, 'platform');
    }

    /**
     * Distinct non-empty values of a whitelisted column, for filter dropdowns.
     *
     * @return string[]
     */
    private function distinctValues(int $idSite, string $column): array
    {
        // $column is from a fixed internal whitelist - never request input.
        if (!in_array($column, ['source', 'environment', 'platform'], true)) {
            return [];
        }

        $qb = Connection::get()->createQueryBuilder();
        $qb->select('DISTINCT ' . $column)->from($this->table())
            ->where('idsite = :idsite')->andWhere($column . " <> ''")->andWhere($column . ' IS NOT NULL')
            ->setParameter('idsite', $idSite)
            ->orderBy($column, 'ASC');

        return $qb->executeQuery()->fetchFirstColumn();
    }

    /**
     * Fetch a single entry, scoped to its project so one project's id can't read another's
     * (IDOR-safe). Returns null when the entry doesn't exist for this project.
     *
     * @return array<string,mixed>|null
     */
    public function getEntry(int $idSite, int $id): ?array
    {
        $qb = Connection::get()->createQueryBuilder();
        $qb->select('*')->from($this->table())
            ->where('idsite = :idsite')->andWhere('idlogentry = :id')
            ->setParameter('idsite', $idSite)
            ->setParameter('id', $id, \Doctrine\DBAL\ParameterType::INTEGER)
            ->setMaxResults(1);

        $row = $qb->executeQuery()->fetchAssociative();
        return $row === false ? null : $row;
    }

    /**
     * The newest/oldest logged_at timestamps for the project (for the default date range).
     *
     * @return array{min:?string,max:?string}
     */
    public function getTimeRange(int $idSite): array
    {
        $qb = Connection::get()->createQueryBuilder();
        $row = $qb->select('MIN(logged_at) AS mn', 'MAX(logged_at) AS mx')
            ->from($this->table())->where('idsite = :idsite')->setParameter('idsite', $idSite)
            ->executeQuery()->fetchAssociative();

        return ['min' => $row['mn'] ?? null, 'max' => $row['mx'] ?? null];
    }

    /** Highest idlogentry across all projects (the ingestion head). */
    public function getMaxEntryId(): int
    {
        $qb = Connection::get()->createQueryBuilder();
        return (int) $qb->select('MAX(idlogentry)')->from($this->table())->executeQuery()->fetchOne();
    }

    /**
     * Entries newer than a cursor across all projects, oldest-first — for the async alert
     * processor (keyset, so it stays cheap regardless of table size).
     *
     * @return array<int,array<string,mixed>>
     */
    public function getNewEntries(int $afterId, int $limit = 5000): array
    {
        $qb = Connection::get()->createQueryBuilder();
        $qb->select('*')->from($this->table())
            ->where('idlogentry > :after')->setParameter('after', $afterId, \Doctrine\DBAL\ParameterType::INTEGER)
            ->orderBy('idlogentry', 'ASC')->setMaxResults($limit);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    private function applyFilters(\Doctrine\DBAL\Query\QueryBuilder $qb, array $filters): void
    {
        if (!empty($filters['level'])) {
            $qb->andWhere('level = :level')->setParameter('level', $this->normaliseLevel($filters['level']));
        }
        if (!empty($filters['source'])) {
            $qb->andWhere('source = :source')->setParameter('source', (string) $filters['source']);
        }
        if (!empty($filters['environment'])) {
            $qb->andWhere('environment = :environment')->setParameter('environment', (string) $filters['environment']);
        }
        if (!empty($filters['platform'])) {
            $qb->andWhere('platform = :platform')->setParameter('platform', (string) $filters['platform']);
        }
        if (!empty($filters['trace_id'])) {
            $qb->andWhere('trace_id = :trace_id')->setParameter('trace_id', (string) $filters['trace_id']);
        }
        if (isset($filters['q']) && $filters['q'] !== '') {
            $qb->andWhere('message LIKE :q')->setParameter('q', '%' . $filters['q'] . '%');
        }
        if (!empty($filters['from'])) {
            $qb->andWhere('logged_at >= :from')->setParameter('from', (string) $filters['from']);
        }
        if (!empty($filters['to'])) {
            $qb->andWhere('logged_at <= :to')->setParameter('to', (string) $filters['to']);
        }
    }

    public function deleteLogsForSite(int $idSite): void
    {
        Connection::get()->delete($this->table(), ['idsite' => $idSite]);
    }

    // -- Rate limiting ------------------------------------------------------------------

    /**
     * Fixed-window per-project rate limit, counted in the option table. Returns false when
     * the project has exceeded $maxPerMinute requests in the current minute.
     */
    public function checkRateLimit(int $idSite, int $maxPerMinute): bool
    {
        if ($maxPerMinute <= 0) {
            return true;
        }

        $window = (int) floor(time() / 60);

        // Fast path: an atomic per-server counter in shared memory (APCu). This avoids a
        // read+write on the contended `option` table on every ingest request. apcu_add seeds
        // the window key with a TTL only if absent; apcu_inc then increments atomically.
        if (function_exists('apcu_enabled') && apcu_enabled()) {
            $key = 'Logs_rate_' . $idSite . '_' . $window;
            apcu_add($key, 0, 120);
            $count = apcu_inc($key);
            if ($count !== false) {
                return (int) $count <= $maxPerMinute;
            }
        }

        // Fallback: a fixed-window counter persisted in the option table (works everywhere,
        // and is shared across app servers, just slower).
        $name   = 'Logs_rate_' . $idSite;
        $conn   = Connection::get();
        $table  = Common::prefixTable('option');

        $current = $conn->createQueryBuilder()->select('option_value')->from($table)
            ->where('option_name = :n')->setParameter('n', $name)
            ->executeQuery()->fetchOne();

        $count = 0;
        if (is_string($current)) {
            [$w, $c] = array_pad(explode(':', $current, 2), 2, '0');
            if ((int) $w === $window) {
                $count = (int) $c;
            }
        }
        $count++;

        $value = $window . ':' . $count;
        if (!$conn->update($table, ['option_value' => $value], ['option_name' => $name])) {
            try {
                $conn->insert($table, ['option_name' => $name, 'option_value' => $value, 'autoload' => 0]);
            } catch (\Throwable $e) {
                $conn->update($table, ['option_value' => $value], ['option_name' => $name]);
            }
        }

        return $count <= $maxPerMinute;
    }

    // -- Per-project ingest tokens ------------------------------------------------------
    //
    // Only a SHA-256 hash of each token is stored, never the token itself: a read of the
    // database alone does not yield usable credentials. The plaintext is shown to the user
    // exactly once, when it is (re)generated.

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function tokenExists(int $idSite): bool
    {
        return Option::get(self::TOKEN_OPTION_PREFIX . $idSite) !== false;
    }

    /**
     * Generate a fresh token, persist only its hash, and return the plaintext once.
     */
    public function regenerateToken(int $idSite): string
    {
        $token = bin2hex(random_bytes(24));
        Option::set(self::TOKEN_OPTION_PREFIX . $idSite, $this->hashToken($token), $autoLoad = 0);
        return $token;
    }

    /**
     * Ensure a token exists. Returns the new plaintext if one had to be created (so the
     * caller can reveal it once), or null if a token already existed.
     */
    public function ensureToken(int $idSite): ?string
    {
        return $this->tokenExists($idSite) ? null : $this->regenerateToken($idSite);
    }

    /**
     * Resolve a presented ingest token to its project id (by hash), or null if unknown.
     */
    public function getSiteIdForToken(string $token): ?int
    {
        if ($token === '') {
            return null;
        }

        $qb = Connection::get()->createQueryBuilder();
        $name = $qb->select('option_name')->from(Common::prefixTable('option'))
            ->where('option_value = :hash')
            ->andWhere('option_name LIKE :prefix')
            ->setParameter('hash', $this->hashToken($token))
            ->setParameter('prefix', self::TOKEN_OPTION_PREFIX . '%')
            ->setMaxResults(1)
            ->executeQuery()->fetchOne();

        if ($name === false) {
            return null;
        }

        return (int) substr($name, strlen(self::TOKEN_OPTION_PREFIX));
    }
}
