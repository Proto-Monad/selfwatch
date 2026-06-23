<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs;

/**
 * Validates and normalises a Sentry-style event payload into a selfwatch log entry.
 *
 * Mirrors the Sentry event data model (event_id, platform, level, logger, transaction,
 * server_name, release, environment, message, exception, tags, extra, contexts, user,
 * breadcrumbs, fingerprint, ...) and enforces Sentry's documented size limits. Every
 * correction is recorded as an "issue" so ingestion can be logged verbosely and the client
 * can be told what was adjusted - without ever rejecting an otherwise-usable event.
 *
 * @see https://develop.sentry.dev/sdk/data-model/event-payloads/
 */
class EventValidator
{
    // Sentry documented limits.
    public const MAX_MESSAGE     = 8192;
    public const MAX_TAG_KEY     = 200;
    public const MAX_TAG_VALUE   = 200;
    public const MAX_TAGS        = 100;
    public const MAX_CONTEXT     = 8192;    // per context object
    public const MAX_EXTRA       = 16384;
    public const MAX_CULPRIT     = 200;
    public const MAX_EVENT_BYTES = 1048576; // 1 MiB whole event

    /** Sentry severity levels, in ascending order. */
    public const SENTRY_LEVELS = ['debug', 'info', 'warning', 'error', 'fatal'];

    /** @var string[] */
    private $issues = [];

    /**
     * @param array<string,mixed> $raw decoded JSON event
     * @return array{entry: array<string,mixed>, issues: string[]}
     */
    public function normalize(array $raw): array
    {
        $this->issues = [];

        $eventId   = $this->normalizeEventId($raw['event_id'] ?? null);
        $level     = $this->normalizeLevel($raw['level'] ?? 'error');
        $platform  = $this->str($raw['platform'] ?? 'other', 64, 'platform');
        $logger    = $this->str($raw['logger'] ?? '', 120, 'logger');
        $transaction = $this->str($raw['transaction'] ?? '', 200, 'transaction');
        $serverName  = $this->str($raw['server_name'] ?? '', 190, 'server_name');
        $environment = $this->str($raw['environment'] ?? 'production', 64, 'environment');
        $release     = $this->str($raw['release'] ?? '', 190, 'release');

        $message = $this->normalizeMessage($raw);
        $tags    = $this->normalizeTags($raw['tags'] ?? null);

        // The "source" shown in the viewer: prefer logger, then transaction, then platform.
        $source = $logger !== '' ? $logger : ($transaction !== '' ? $transaction : $platform);

        // Trace id: a real distributed-trace id if present, else the event id.
        $traceId = $this->extractTraceId($raw) ?? $eventId;

        // Everything the columns don't capture is preserved as structured context.
        $context = $this->buildContext($raw, $tags, $transaction);

        $entry = [
            'kind'        => 'event',
            'event_id'    => $eventId,
            'level'       => $level,
            'message'     => $message,
            'source'      => $source,
            'platform'    => $platform,
            'environment' => $environment,
            'app_release' => $release !== '' ? $release : null,
            'host'        => $serverName !== '' ? $serverName : null,
            'trace_id'    => $traceId,
            'timestamp'   => $raw['timestamp'] ?? null, // normalised to logged_at downstream
            'context'     => $context,
        ];

        return ['entry' => $entry, 'issues' => $this->issues];
    }

    private function normalizeEventId($value): string
    {
        $value = is_string($value) ? strtolower(str_replace('-', '', $value)) : '';
        if (preg_match('/^[0-9a-f]{32}$/', $value)) {
            return $value;
        }
        if ($value !== '') {
            $this->issues[] = 'event_id was not a 32-char hex uuid; generated a new one';
        }
        return bin2hex(random_bytes(16));
    }

    private function normalizeLevel($level): string
    {
        $level = strtolower(trim((string) $level));
        // Map Sentry's "fatal" onto selfwatch's "critical"; "warn" alias.
        $map = ['fatal' => 'critical', 'warn' => 'warning', 'err' => 'error'];
        $level = $map[$level] ?? $level;

        if (!in_array($level, Model::LEVELS, true)) {
            $this->issues[] = sprintf('unknown level "%s"; defaulted to error', $level);
            return 'error';
        }
        return $level;
    }

    /**
     * The Sentry "message" can be a plain string or a {message, params, formatted} object.
     * For exception events with no message, derive one from the first exception value.
     */
    private function normalizeMessage(array $raw): string
    {
        $message = '';
        $m = $raw['message'] ?? null;

        if (is_string($m)) {
            $message = $m;
        } elseif (is_array($m)) {
            $message = (string) ($m['formatted'] ?? $m['message'] ?? '');
        }

        if ($message === '' && isset($raw['exception'])) {
            $message = $this->messageFromException($raw['exception']);
        }

        if ($message === '') {
            $this->issues[] = 'event had no message; stored empty';
        }
        if (strlen($message) > self::MAX_MESSAGE) {
            $this->issues[] = sprintf('message truncated to %d chars', self::MAX_MESSAGE);
            $message = substr($message, 0, self::MAX_MESSAGE);
        }
        return $message;
    }

    private function messageFromException($exception): string
    {
        // Sentry exception interface: {values: [{type, value, ...}, ...]} (or a bare list).
        $values = $exception['values'] ?? (is_array($exception) ? $exception : []);
        $last   = is_array($values) ? end($values) : null;
        if (is_array($last)) {
            $type  = (string) ($last['type'] ?? 'Exception');
            $value = (string) ($last['value'] ?? '');
            return trim($value === '' ? $type : "$type: $value");
        }
        return '';
    }

    /**
     * @param mixed $tags object or list of [key, value] pairs
     * @return array<string,string>
     */
    private function normalizeTags($tags): array
    {
        if (empty($tags) || !is_array($tags)) {
            return [];
        }

        // Accept both {k: v} and [[k, v], ...].
        $pairs = [];
        if (array_keys($tags) === range(0, count($tags) - 1)) {
            foreach ($tags as $pair) {
                if (is_array($pair) && count($pair) === 2) {
                    $pairs[(string) $pair[0]] = $pair[1];
                }
            }
        } else {
            $pairs = $tags;
        }

        $out = [];
        foreach ($pairs as $key => $value) {
            if (count($out) >= self::MAX_TAGS) {
                $this->issues[] = sprintf('too many tags; kept first %d', self::MAX_TAGS);
                break;
            }
            $key = (string) $key;
            $value = is_scalar($value) ? (string) $value : json_encode($value);
            if (strlen($key) > self::MAX_TAG_KEY || strlen((string) $value) > self::MAX_TAG_VALUE) {
                $this->issues[] = sprintf('tag "%s" exceeded length limit and was truncated', substr($key, 0, 32));
                $key = substr($key, 0, self::MAX_TAG_KEY);
                $value = substr((string) $value, 0, self::MAX_TAG_VALUE);
            }
            $out[$key] = $value;
        }
        return $out;
    }

    private function extractTraceId(array $raw): ?string
    {
        $trace = $raw['contexts']['trace']['trace_id'] ?? null;
        if (is_string($trace) && $trace !== '') {
            return substr(strtolower(str_replace('-', '', $trace)), 0, 64);
        }
        return null;
    }

    /**
     * Assemble the structured context blob stored alongside the entry: the Sentry interfaces
     * that don't map to a column. Oversized sub-objects are dropped with an issue recorded.
     *
     * @return string JSON
     */
    private function buildContext(array $raw, array $tags, string $transaction): string
    {
        $context = [];

        if ($tags) {
            $context['tags'] = $tags;
        }
        if ($transaction !== '') {
            $context['transaction'] = $transaction;
        }

        foreach (['exception', 'stacktrace', 'user', 'request', 'breadcrumbs', 'modules', 'fingerprint', 'sdk'] as $key) {
            if (isset($raw[$key])) {
                $context[$key] = $raw[$key];
            }
        }

        $extra = $raw['extra'] ?? null;
        if (is_array($extra)) {
            $encoded = json_encode($extra);
            if (strlen((string) $encoded) > self::MAX_EXTRA) {
                $this->issues[] = 'extra exceeded size limit and was dropped';
            } else {
                $context['extra'] = $extra;
            }
        }

        $contexts = $raw['contexts'] ?? null;
        if (is_array($contexts)) {
            $kept = [];
            foreach ($contexts as $name => $ctx) {
                if (strlen((string) json_encode($ctx)) > self::MAX_CONTEXT) {
                    $this->issues[] = sprintf('context "%s" exceeded %d bytes and was dropped', $name, self::MAX_CONTEXT);
                    continue;
                }
                $kept[$name] = $ctx;
            }
            if ($kept) {
                $context['contexts'] = $kept;
            }
        }

        return (string) json_encode($context);
    }

    /**
     * Validate + truncate a scalar string field, recording an issue on truncation.
     */
    private function str($value, int $max, string $name): string
    {
        if (!is_scalar($value)) {
            return '';
        }
        $value = trim((string) $value);
        if (strlen($value) > $max) {
            $this->issues[] = sprintf('%s truncated to %d chars', $name, $max);
            $value = substr($value, 0, $max);
        }
        return $value;
    }
}
