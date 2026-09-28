# christianbrown Database ORM

[![CI](https://github.com/christianjbrown/christianbrown-database-orm-php/actions/workflows/ci.yml/badge.svg)](https://github.com/christianjbrown/christianbrown-database-orm-php/actions/workflows/ci.yml) [![License](https://img.shields.io/github/license/christianjbrown/christianbrown-database-orm-php)](https://github.com/christianjbrown/christianbrown-database-orm-php/blob/main/LICENSE) [![PHP](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2Fchristianjbrown%2Fchristianbrown-database-orm-php%2Fmain%2Fcomposer.json&query=%24.require.php&label=php&color=777BB4)](https://github.com/christianjbrown/christianbrown-database-orm-php/blob/main/composer.json)

Shared Doctrine ORM plumbing for the personal `christianbrown` schema on the shared Cloud SQL (MySQL)
instance. It is consumed by the christianbrown Google Cloud Run functions so the entity mapping, the
`EntityManager` bootstrap, and the climate-write logic live in one place instead of being copied between
services.

## What lives here

- **`src/Entity/`** — the entities, using PHP 8 attributes:
  - `RefreshToken` — the rotating OAuth token key-value row (`refresh_tokens`), used by
    `cloud-run-function-smartthings-climate`.
  - `SmartThingsClimate` (`smartthings_climate`) and `MetOfficeWeather` (`met_office_weather`) — the
    append-only climate history tables (`recorded_at`, `temperature`, `humidity`), written every time
    a request reaches the origin of each function. A future `historical-climate-data` function reads
    these to return per-hour / per-day min & max over a time range.
- **`src/EntityManagerFactory.php`** — builds a Doctrine `EntityManager` from a DSN string (with a short
  connect timeout so a database outage cannot hang a request).
- **`src/ClimateMeasurementRecorder.php`** — the shared write: `persist()` + `flush()` a
  climate-reading entity. Callers wrap it in their own `try/catch` so a write failure never disturbs the
  HTTP response.

## Consuming it

This package isn't on Packagist, so add a `dev-main` requirement plus a GitHub VCS `repositories`
entry pointing at it — and one for its transitive dependency `christianjbrown/key-value-store`. Both
repositories are public, so Composer needs no authentication to fetch them:

```json
"require": { "christianjbrown/christianbrown-database-orm": "dev-main" },
"repositories": [
    { "type": "github", "url": "https://github.com/christianjbrown/christianbrown-database-orm-php.git" },
    { "type": "github", "url": "https://github.com/christianjbrown/key-value-store-php.git" }
]
```

Credentials reach the function as a DSN environment variable, e.g.

```
mysql://user:password@localhost/schema?unix_socket=/tmp/cloudsql/project:region:instance&driver=pdo_mysql
```

## Schema management

Schema is managed here with the Doctrine schema tool via the root `doctrine` console entry point.

**The `christianbrown` schema is shared with other personal projects, so never run
`orm:schema-tool:update --complete --force` against it** — that would drop tables this package does not
model. Use `composer db-update-dry-run` (`--dump-sql`, additive) to review the DDL, then apply it
out-of-band (Cloud SQL client over the proxy), the same way `refresh_tokens` is managed.
