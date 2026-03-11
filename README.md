# logs.philipnewborough.co.uk

A centralised **application event log service** built with [CodeIgniter 4](https://codeigniter.com/). It is one microservice within a larger multi-application platform. Other apps POST structured log entries to it via a REST API; administrators view and manage those entries through a web-based Event Viewer dashboard.

---

## Features

- **REST API** — accepts inbound log entries from any platform application authenticated with an API key.
- **Event Viewer** — server-side DataTables dashboard for browsing, filtering, and bulk-deleting log entries.
- **Log levels** — Info, Warning, Error, Critical, Debug, rendered as colour-coded Bootstrap 5 badges.
- **Live stats** — stat cards for each log level refresh without a page reload.
- **Timezone-aware display** — stored UTC timestamps are converted to the viewer's browser timezone.
- **Centralised auth integration** — delegates authentication to an external auth service over cURL; no local user accounts.
- **Reusable `logit()` helper** — any platform app can include `log_helper.php` to POST log events here with a single function call.
- **Debug tools** — admin-only diagnostics for PHP info, session data, path constants, and service connectivity tests.

---

## Requirements

- PHP ≥ 8.2
- MySQL / MariaDB
- Composer

---

## Installation

```bash
git clone <repo-url>
cd codeigniter
composer install
cp env .env
```

Edit `.env` and set at minimum:

```ini
# Application
app.baseURL = 'https://logs.philipnewborough.co.uk/'

# Database
database.default.hostname = localhost
database.default.database = logs
database.default.username = <db-user>
database.default.password = <db-password>

# API / Auth integration
apikeys.masterKey = <shared-master-key>

# Platform service URLs
urls.tld          = https://philipnewborough.co.uk/
urls.auth         = https://auth.philipnewborough.co.uk/
urls.logs         = https://logs.philipnewborough.co.uk/
urls.assets       = https://assets.philipnewborough.co.uk/
urls.tldcookiedomain = .philipnewborough.co.uk
urls.cookiedomain    = logs.philipnewborough.co.uk
```

Run the database migration:

```bash
php spark migrate
```

---

## Routes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/` | Redirects admins to `/admin`, others to the platform TLD |
| GET | `/admin` | Event Viewer dashboard |
| GET | `/admin/datatable` | Server-side DataTables JSON feed |
| POST | `/admin/logs/delete` | Bulk delete logs by ID array |
| GET | `/admin/stats` | JSON log-level counts (used for live stat refresh) |
| GET, OPTIONS | `/api/test/ping` | API health check — returns `pong` |
| POST, OPTIONS | `/api/log` | Ingest a log entry |
| GET | `/logout` | Destroys session, redirects to auth service |
| GET | `/unauthorised` | 403 page |
| GET | `/debug` | Debug diagnostics home (admin only) |
| GET | `/debug/(:segment)` | Dynamic debug tool routing (admin only) |

---

## API

### Authentication

All `/api/*` routes require an `apikey` request header containing the master API key (`ApiKeys::$masterKey`). Requests with an absent or non-matching key are rejected with HTTP 401.

### `POST /api/log`

```http
POST /api/log
apikey: <master-api-key>
Content-Type: application/json

{
  "message": "Something happened",
  "level": 0,
  "domain": "example.philipnewborough.co.uk"
}
```

**Log levels:** `0` = Info, `1` = Warning, `2` = Error, `3` = Critical, `4` = Debug

**Response (200):**
```json
{ "success": "Log data inserted successfully" }
```

### `GET /api/test/ping`

```http
GET /api/test/ping
apikey: <master-api-key>
```

**Response (200):**
```json
{ "status": "success", "message": "pong" }
```

### `logit()` helper

Any platform application can include `app/Helpers/log_helper.php` and call:

```php
logit('Something happened', 2); // level 2 = Error
```

This POSTs the entry to `Urls::$logs` using the master key automatically.

---

## Database Schema

Table: `logs`

| Column | Type | Notes |
|--------|------|-------|
| `id` | INT(11) UNSIGNED | Auto-increment primary key |
| `message` | VARCHAR(255) | Log message (truncated on ingest) |
| `level` | TINYINT(4) | 0 = Info, 1 = Warning, 2 = Error, 3 = Critical, 4 = Debug |
| `domain` | VARCHAR(255) | Originating application domain |
| `created_at` | DATETIME | UTC, auto-set on insert |
| `updated_at` | DATETIME | UTC, auto-updated |

---

## Authentication

Authentication is delegated entirely to the platform's centralised auth service. There are no local user accounts.

- The `AuthFilter` (applied globally, except to `/`, `cli/*`, `api/*`, and `/unauthorised`) reads `user_uuid` and `token` cookies. If the session is stale it validates them against the auth service via cURL and hydrates the session.
- The `AdminFilter` (applied to `admin/*`) checks `session()->get('is_admin')` and redirects to `/unauthorised` if false.

---

## Development

### Linting

```bash
npx eslint public/assets/js/
```

### Testing

```bash
composer test
```

Tests use PHPUnit 10.5. The test suite currently covers:

- `HealthTest` — verifies `APPPATH` is defined and `baseURL` is a valid URL.
- Database and session example stubs in `tests/database/` and `tests/session/`.

---

## Dependencies

### PHP (Composer)

| Package | Purpose |
|---------|---------|
| `codeigniter4/framework ^4.7` | MVC framework |
| `hermawan/codeigniter4-datatables ^0.8` | Server-side DataTables integration |
| `ramsey/uuid ^4.9` | UUID generation |

### JavaScript (npm, dev only)

| Package | Purpose |
|---------|---------|
| `eslint ^10` | JavaScript linting |

---

## Licence

Application source code: [MIT](LICENSE)  
CodeIgniter 4 framework: [MIT](LICENSE-CODEIGNITER)

