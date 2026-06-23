<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs;

/**
 * Sentry-compatible event ingestion: turns a Sentry "store" event or an envelope into
 * stored selfwatch entries, with thorough validation (via {@see EventValidator}) and
 * verbose, correlated logging (via {@see IngestLogger}).
 *
 * @see https://develop.sentry.dev/sdk/data-model/event-payloads/
 * @see https://develop.sentry.dev/sdk/data-model/envelopes/
 */
class SentryIngest
{
    /** @var IngestLogger */
    private $log;

    public function __construct(IngestLogger $log)
    {
        $this->log = $log;
    }

    /**
     * Ingest a single Sentry event (the body of a /store/ request).
     *
     * @param array<string,mixed> $event
     * @return string the event id
     */
    public function ingestEvent(int $idSite, array $event): string
    {
        $result = (new EventValidator())->normalize($event);
        $entry  = $result['entry'];

        $this->log->issues($result['issues']);
        $this->log->debug('validated event', [
            'event_id'    => $entry['event_id'],
            'level'       => $entry['level'],
            'platform'    => $entry['platform'],
            'environment' => $entry['environment'],
            'issues'      => count($result['issues']),
        ]);

        $stored = (new Model())->insertBatch($idSite, [$entry]);
        $this->log->info('stored event', ['event_id' => $entry['event_id'], 'rows' => count($stored)]);

        // Alerts are evaluated asynchronously by the logs:process-alerts command / scheduled
        // task - keeping ingestion fast and decoupled from webhook/email delivery.

        return $entry['event_id'];
    }

    /**
     * Ingest a Sentry envelope (the body of an /envelope/ request): a header line followed
     * by (item-header, item-payload) line pairs. Only "event" items are stored; other item
     * types (transaction, session, attachment, ...) are acknowledged and skipped.
     *
     * @return string the last event id processed (or the envelope's event_id)
     */
    public function ingestEnvelope(int $idSite, string $body): string
    {
        $lines = explode("\n", trim($body));
        if (empty($lines[0])) {
            throw new \RuntimeException('Empty envelope.');
        }

        $envelopeHeader = json_decode($lines[0], true);
        $lastEventId    = is_array($envelopeHeader) ? (string) ($envelopeHeader['event_id'] ?? '') : '';
        $this->log->debug('envelope header', ['items' => max(0, (count($lines) - 1) / 2)]);

        $i = 1;
        $count = count($lines);
        while ($i < $count) {
            $itemHeader = json_decode($lines[$i], true);
            $payloadRaw = $lines[$i + 1] ?? '';
            $i += 2;

            if (!is_array($itemHeader)) {
                continue;
            }
            $type = (string) ($itemHeader['type'] ?? '');
            if ($type !== 'event') {
                $this->log->debug('skipping envelope item', ['type' => $type]);
                continue;
            }

            $event = json_decode($payloadRaw, true);
            if (is_array($event)) {
                $lastEventId = $this->ingestEvent($idSite, $event);
            } else {
                $this->log->warning('envelope event item was not valid JSON');
            }
        }

        return $lastEventId;
    }
}
