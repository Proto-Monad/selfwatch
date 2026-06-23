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

/**
 * Data layer for alert rules and the fired-alert event log. Like {@see Model}, everything
 * goes through Doctrine DBAL's query builder (portable, no engine-specific SQL).
 */
class AlertModel
{
    public const CHANNELS = ['webhook', 'email'];

    private function rulesTable(): string
    {
        return Common::prefixTable('alert_rule');
    }

    private function eventsTable(): string
    {
        return Common::prefixTable('alert_event');
    }

    // -- Rules --------------------------------------------------------------------------

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getRules(int $idSite, bool $onlyEnabled = false): array
    {
        $qb = Connection::get()->createQueryBuilder();
        $qb->select('*')->from($this->rulesTable())
            ->where('idsite = :idsite')->setParameter('idsite', $idSite)
            ->orderBy('idalertrule', 'ASC');
        if ($onlyEnabled) {
            $qb->andWhere('enabled = 1');
        }
        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Load a rule, scoped to its project. The $idSite scope enforces object-level
     * authorization: a user can only act on rules belonging to a project they're
     * authorized for, preventing IDOR via a crafted rule id.
     */
    public function getRule(int $idRule, int $idSite): ?array
    {
        $qb = Connection::get()->createQueryBuilder();
        $row = $qb->select('*')->from($this->rulesTable())
            ->where('idalertrule = :id')->andWhere('idsite = :idsite')
            ->setParameter('id', $idRule)->setParameter('idsite', $idSite)
            ->executeQuery()->fetchAssociative();
        return $row === false ? null : $row;
    }

    public function createRule(int $idSite, array $data): int
    {
        $conn = Connection::get();
        $conn->insert($this->rulesTable(), $this->sanitize($data, $idSite) + [
            'created_at'    => Date::now()->getDatetime(),
            'last_fired_at' => null,
        ]);
        return (int) $conn->lastInsertId();
    }

    public function updateRule(int $idRule, int $idSite, array $data): void
    {
        // The idsite criterion ensures a rule can only be updated within its own project.
        Connection::get()->update(
            $this->rulesTable(),
            $this->sanitize($data, $idSite),
            ['idalertrule' => $idRule, 'idsite' => $idSite]
        );
    }

    public function deleteRule(int $idRule, int $idSite): void
    {
        Connection::get()->delete($this->rulesTable(), ['idalertrule' => $idRule, 'idsite' => $idSite]);
    }

    public function setEnabled(int $idRule, int $idSite, bool $enabled): void
    {
        Connection::get()->update(
            $this->rulesTable(),
            ['enabled' => $enabled ? 1 : 0],
            ['idalertrule' => $idRule, 'idsite' => $idSite]
        );
    }

    public function markFired(int $idRule, string $when): void
    {
        Connection::get()->update($this->rulesTable(), ['last_fired_at' => $when], ['idalertrule' => $idRule]);
    }

    /**
     * Whitelist + normalise rule fields coming from a request.
     */
    private function sanitize(array $data, int $idSite): array
    {
        $level   = (new Model())->normaliseLevel($data['min_level'] ?? 'error');
        $channel = in_array($data['channel'] ?? '', self::CHANNELS, true) ? $data['channel'] : 'webhook';
        $target  = substr(trim((string) ($data['channel_target'] ?? '')), 0, 500);

        // Validate the delivery target up front. The webhook URL is later fetched
        // server-side, so it must be a well-formed http(s) URL (no file://, gopher://, ...).
        if ($channel === 'webhook') {
            if (!preg_match('#^https?://#i', $target) || filter_var($target, FILTER_VALIDATE_URL) === false) {
                throw new \Exception('The webhook target must be a valid http(s) URL.');
            }
            // Reject the link-local range up front (cloud metadata, e.g. 169.254.169.254).
            // Hostname-based bypasses are also blocked at delivery time in AlertEngine.
            if (strpos((string) parse_url($target, PHP_URL_HOST), '169.254.') === 0) {
                throw new \Exception('The webhook host is not allowed.');
            }
        } elseif ($channel === 'email') {
            if (filter_var($target, FILTER_VALIDATE_EMAIL) === false) {
                throw new \Exception('The email target must be a valid email address.');
            }
        }

        return [
            'idsite'           => $idSite,
            'name'             => substr(trim((string) ($data['name'] ?? 'Alert')), 0, 190),
            'enabled'          => !empty($data['enabled']) ? 1 : 0,
            'min_level'        => $level,
            'source'           => substr(trim((string) ($data['source'] ?? '')), 0, 120),
            'message_contains' => substr((string) ($data['message_contains'] ?? ''), 0, 255),
            'channel'          => $channel,
            'channel_target'   => $target,
            'cooldown_seconds' => max(0, (int) ($data['cooldown_seconds'] ?? 0)),
        ];
    }

    // -- Events -------------------------------------------------------------------------

    public function recordEvent(array $data): void
    {
        Connection::get()->insert($this->eventsTable(), [
            'idalertrule' => (int) $data['idalertrule'],
            'idsite'      => (int) $data['idsite'],
            'idlogentry'  => isset($data['idlogentry']) ? (int) $data['idlogentry'] : null,
            'level'       => (string) $data['level'],
            'message'     => (string) $data['message'],
            'channel'     => (string) $data['channel'],
            'target'      => (string) $data['target'],
            'status'      => (string) $data['status'],
            'error'       => $data['error'] ?? null,
            'fired_at'    => Date::now()->getDatetime(),
        ]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getRecentEvents(int $idSite, int $limit = 50): array
    {
        $qb = Connection::get()->createQueryBuilder();
        return $qb->select('e.*', 'r.name AS rule_name')
            ->from($this->eventsTable(), 'e')
            ->leftJoin('e', $this->rulesTable(), 'r', 'e.idalertrule = r.idalertrule')
            ->where('e.idsite = :idsite')->setParameter('idsite', $idSite)
            ->orderBy('e.idalertevent', 'DESC')
            ->setMaxResults($limit)
            ->executeQuery()->fetchAllAssociative();
    }
}
