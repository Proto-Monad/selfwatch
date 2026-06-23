<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs;

use Piwik\Date;
use Piwik\Mail;
use Piwik\Option;

/**
 * Evaluates alert rules against freshly-ingested log entries and dispatches notifications.
 *
 * A rule fires when an entry's severity is at least its `min_level` and (optionally) the
 * source matches and the message contains a substring. Each rule fires at most once per
 * ingest batch and is throttled by its `cooldown_seconds`.
 */
class AlertEngine
{
    private const CURSOR_OPTION = 'Logs_alert_cursor';

    /**
     * Process entries ingested since the last run and fire matching alerts. This runs OFF the
     * ingest request path - via the scheduled task (hourly) or, for tighter latency, a cron
     * running `./console logs:process-alerts` (e.g. every minute). Returns entries processed.
     */
    public function processNew(int $batch = 5000): int
    {
        $model  = new Model();
        $logger = new IngestLogger();
        $cursor = (int) Option::get(self::CURSOR_OPTION);

        if ($cursor === 0) {
            // First run: start at the current head so we don't replay the whole history.
            Option::set(self::CURSOR_OPTION, (string) $model->getMaxEntryId(), false);
            return 0;
        }

        $rows = $model->getNewEntries($cursor, $batch);
        if (empty($rows)) {
            return 0;
        }

        $bySite = [];
        $maxId  = $cursor;
        foreach ($rows as $row) {
            $bySite[(int) $row['idsite']][] = $row;
            $maxId = max($maxId, (int) $row['idlogentry']);
        }

        foreach ($bySite as $idSite => $entries) {
            try {
                $this->evaluate((int) $idSite, $entries);
            } catch (\Throwable $e) {
                // Self-monitoring: one failing channel/rule must not stall the processor.
                $logger->error('alert processing failed', ['idsite' => $idSite, 'error' => $e->getMessage()]);
            }
        }

        Option::set(self::CURSOR_OPTION, (string) $maxId, false);
        return count($rows);
    }

    /**
     * @param array<int,array<string,mixed>> $entries normalised, stored entries (with idlogentry)
     */
    public function evaluate(int $idSite, array $entries): void
    {
        if (empty($entries)) {
            return;
        }

        $alertModel = new AlertModel();
        $rules      = $alertModel->getRules($idSite, true);
        if (empty($rules)) {
            return;
        }

        $model = new Model();
        $now   = time();

        foreach ($rules as $rule) {
            // Throttle: skip if the cooldown window since the last firing hasn't elapsed.
            if (!empty($rule['last_fired_at'])) {
                $last = strtotime((string) $rule['last_fired_at']);
                if ($last !== false && ($now - $last) < (int) $rule['cooldown_seconds']) {
                    continue;
                }
            }

            $minRank = $model->levelRank((string) $rule['min_level']);

            foreach ($entries as $entry) {
                if (!$this->matches($rule, $entry, $model, $minRank)) {
                    continue;
                }

                $result = $this->dispatch($rule, $entry, $idSite);

                $alertModel->recordEvent([
                    'idalertrule' => $rule['idalertrule'],
                    'idsite'      => $idSite,
                    'idlogentry'  => $entry['idlogentry'] ?? null,
                    'level'       => $entry['level'] ?? '',
                    'message'     => $entry['message'] ?? '',
                    'channel'     => $rule['channel'],
                    'target'      => $rule['channel_target'],
                    'status'      => $result['status'],
                    'error'       => $result['error'],
                ]);
                $alertModel->markFired((int) $rule['idalertrule'], Date::now()->getDatetime());

                // One firing per rule per batch.
                break;
            }
        }
    }

    private function matches(array $rule, array $entry, Model $model, int $minRank): bool
    {
        if ($model->levelRank((string) ($entry['level'] ?? 'info')) < $minRank) {
            return false;
        }
        if (!empty($rule['source']) && (string) ($entry['source'] ?? '') !== (string) $rule['source']) {
            return false;
        }
        if (!empty($rule['message_contains'])
            && stripos((string) ($entry['message'] ?? ''), (string) $rule['message_contains']) === false) {
            return false;
        }
        return true;
    }

    /**
     * @return array{status:string,error:?string}
     */
    private function dispatch(array $rule, array $entry, int $idSite): array
    {
        try {
            if (($rule['channel'] ?? '') === 'email') {
                $this->sendEmail($rule, $entry, $idSite);
            } else {
                $this->sendWebhook($rule, $entry, $idSite);
            }
            return ['status' => 'sent', 'error' => null];
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'error' => substr($e->getMessage(), 0, 500)];
        }
    }

    private function payload(array $rule, array $entry, int $idSite): array
    {
        $context = $entry['context'] ?? null;
        if (is_string($context)) {
            $decoded = json_decode($context, true);
            $context = $decoded === null ? $context : $decoded;
        }

        return [
            'alert'     => $rule['name'],
            'project'   => $idSite,
            'level'     => $entry['level'] ?? '',
            'source'    => $entry['source'] ?? '',
            'message'   => $entry['message'] ?? '',
            'trace_id'  => $entry['trace_id'] ?? null,
            'logged_at' => $entry['logged_at'] ?? null,
            'context'   => $context,
        ];
    }

    private function sendWebhook(array $rule, array $entry, int $idSite): void
    {
        $url = (string) ($rule['channel_target'] ?? '');
        if ($url === '') {
            throw new \Exception('No webhook URL configured.');
        }

        // Defense in depth against SSRF (rules are admin-only, but block the obvious cases):
        // only http(s), and never the link-local 169.254.0.0/16 range (cloud instance
        // metadata endpoints such as 169.254.169.254).
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new \Exception('Webhook URL must use http or https.');
        }
        $host = (string) parse_url($url, PHP_URL_HOST);
        $ip   = filter_var($host, FILTER_VALIDATE_IP) ? $host : @gethostbyname($host);
        if (is_string($ip) && strpos($ip, '169.254.') === 0) {
            throw new \Exception('Webhook URL targets a disallowed address.');
        }

        $body    = json_encode($this->payload($rule, $entry, $idSite));
        $context = stream_context_create(['http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json\r\nUser-Agent: selfwatch-alerts\r\n",
            'content'       => $body,
            'timeout'       => 5,
            'ignore_errors' => true,
        ]]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            throw new \Exception('Webhook request failed (connection error).');
        }

        $status = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $status = (int) $m[1];
        }
        if ($status >= 400) {
            throw new \Exception("Webhook returned HTTP $status.");
        }
    }

    private function sendEmail(array $rule, array $entry, int $idSite): void
    {
        $to = (string) ($rule['channel_target'] ?? '');
        if ($to === '') {
            throw new \Exception('No email recipient configured.');
        }

        $mail = new Mail();
        $mail->setDefaultFromPiwik();
        $mail->addTo($to);
        $mail->setSubject('[selfwatch] ' . strtoupper((string) ($entry['level'] ?? '')) . ' · ' . $rule['name']);
        $mail->setBodyText(
            "Alert rule: {$rule['name']}\n"
            . 'Project: ' . $idSite . "\n"
            . 'Level: ' . ($entry['level'] ?? '') . "\n"
            . 'Source: ' . ($entry['source'] ?? '') . "\n"
            . 'Message: ' . ($entry['message'] ?? '') . "\n"
            . 'Trace: ' . ($entry['trace_id'] ?? '-') . "\n"
            . 'Time: ' . ($entry['logged_at'] ?? '')
        );
        $mail->send();
    }
}
