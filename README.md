# selfwatch

**Self-hosted log & event tracking.**

selfwatch is a self-hosted platform for collecting, searching and alerting on logs and error
events from any source — PHP, Python, Rust, JavaScript, or plain `curl`. It speaks a
**Sentry-compatible ingestion API**, so existing Sentry SDKs (and the selfwatch SDKs) can
point at your own server with just a DSN change, and your data never leaves your infrastructure.

It is a focused fork of [Matomo](https://matomo.org). The analytics plugins are gone; what remains is a log tracker.

---

## Features

- **Sentry-compatible ingestion** — `POST /api/{project}/store/` (single event) and
  `/api/{project}/envelope/`, with thorough validation, normalization and verbose, correlated
  ingest logging. A simpler native endpoint (`ingest.php`) is also available.
- **First-class SDKs** — [PHP](https://github.com/Proto-Monad/selfwatch-php-sdk),
  [JavaScript/Node](https://github.com/Proto-Monad/selfwatch-js-sdk) and
  [Rust](https://github.com/Proto-Monad/selfwatch-rust-sdk), all write-only and transport-safe
  (logging never crashes your app).
- **A real log viewer** — dense, filterable table (level · source · environment · platform ·
  full-text search · trace id · date range), **keyset pagination** that scales to millions of
  rows, and **live tail**.
- **Event detail pages** — full message, a metadata grid, **exception stack traces** (with
  in-app frame highlighting), tags, user/request, breadcrumbs and the raw payload.
- **Alerts** — per-project rules (minimum severity, source, message substring) delivered to
  **webhooks** (SSRF-guarded) or **email** (via your Matomo mail settings), with cooldowns.
  Evaluated **asynchronously**, off the ingest request path.
- **Projects & tokens** — each project (a Matomo "site") has a hashed, **write-only** ingest
  token you can rotate; a leaked token can submit events but can never read your logs.
- **SQLite or MySQL/MariaDB** — choose at install. All data access goes through Doctrine DBAL,
  so the same code runs on either engine (no hand-written SQL).

---

## Quick start (development)

Requirements: PHP 8.1+ with `pdo_sqlite` (or `pdo_mysql`), and the bundled dependencies.

```bash
# from the app directory
php -S localhost:8003 router.php
```

Open <http://localhost:8003/> and complete the installer (pick SQLite for a zero-setup dev DB).
The dev installer seeds an admin user — **`admin` / `admin123`** — change this before exposing
the instance. The `router.php` dev server maps the clean `/api/{id}/store/` URLs to the
ingestion endpoint; in production you do this with an nginx/Apache rewrite (see *Deployment*).

---

## Sending logs

Every project has a **DSN**:

```
{scheme}://{TOKEN}@{HOST}/{PROJECT_ID}
http://<token>@localhost:8003/1
```

Get or rotate the token on **Logs → Projects & tokens** (admin only). Then either drop in an
SDK or POST directly:

```bash
curl -X POST http://localhost:8003/api/1/store/ \
  -H "Authorization: Bearer <your-token>" \
  -H "Content-Type: application/json" \
  -d '{"level":"error","message":"Payment failed","platform":"curl",
       "environment":"production","extra":{"order":123}}'
```

The SDKs build the URL from the DSN automatically:

| Language | Package | Repository |
|---|---|---|
| PHP | `selfwatch/sdk` | <https://github.com/Proto-Monad/selfwatch-php-sdk> |
| JavaScript / Node | `@selfwatch/sdk` | <https://github.com/Proto-Monad/selfwatch-js-sdk> |
| Rust | `selfwatch` | <https://github.com/Proto-Monad/selfwatch-rust-sdk> |

> `http` is only accepted for loopback hosts; any non-loopback host **must** use `https`
> (enforced both by the server and the SDKs).

---

## Alerts

Create rules under **Logs → Alerts**: fire when an entry is at least *level* X, optionally with
a *source* and a *message contains* substring, throttled by a cooldown. Channels:

- **Webhook** — `POST`s the event JSON; outbound requests are SSRF-guarded (no link-local /
  internal targets).
- **Email** — sent through Matomo's configured mail transport (**Settings → General → Mail
  Server Settings**), so SMTP is whatever you set there.

Alerts are evaluated **asynchronously** so ingestion stays fast. Run the processor from cron
for low latency:

```cron
* * * * * php /path/to/selfwatch/console logs:process-alerts >/dev/null 2>&1
```

(Without the cron, the hourly scheduled task still runs them.)

---

## Security model

Logs are sensitive, so the ingest credential is deliberately **write-only**:

- Ingest tokens are stored only as a **SHA-256 hash**; the plaintext is shown once at creation.
- A token can **only submit events** — it cannot read logs, list projects, or authenticate to
  any reporting API (it is never written to Matomo's user-token table). A leaked token's worst
  case is event spam, which the **per-project rate limit** caps.
- HTTPS is enforced for ingestion (loopback exempt), the request body is capped, and all
  read/management screens require an authenticated Matomo session with the right permission.
- Project data access is **IDOR-scoped** end to end (one project can't read another's entries
  or alert rules), and admin actions are CSRF-nonce protected.

For a public/browser DSN, treat the token as public and write-only (as you would a Sentry DSN);
your server SDKs keep theirs in env/secrets.

---

## Deployment

1. **Web server rewrite** for the Sentry-style URLs (nginx example):
   ```nginx
   location ~ ^/api/[0-9]+/(store|envelope)/?$ { rewrite ^ /api.php last; }
   ```
   On Apache the bundled root `.htaccess` handles this when `mod_rewrite` is enabled.
2. **Database** — SQLite is fine for small/single-node setups; choose MySQL/MariaDB at install
   for higher volume.
3. **Alert cron** — schedule `console logs:process-alerts` (above).
4. **Production config** — set a real `salt`, `trusted_hosts`, HTTPS, and change the admin
   password.

---

## Development

```bash
php -S localhost:8003 router.php      # run the dev server
php plugins/Logs/tests/run.php        # run the selfwatch test suite
```

The selfwatch logic lives in **`plugins/Logs`** (ingestion, validation, viewer, alerts, tokens),
the Sentry endpoint in **`api.php`**, the DBAL layer in **`core/Db/Dbal`** and the engine schemas
in **`core/Db/Schema`** (shared, DBAL-generated). `plugins/Logs/tests/run.php` is a
dependency-free runner covering the validation and authorization-critical paths.

---

## License

selfwatch is released under the **GPL v3 (or later)**, inheriting Matomo's license. It is an
independent fork and is not affiliated with or endorsed by Matomo. See [LICENSE](LICENSE).
