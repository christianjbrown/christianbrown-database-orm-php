# christianbrown Database ORM

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

Add a `dev-main` requirement plus a GitHub VCS `repositories` entry (this package is **not** on
Packagist), and a `repositories` entry for its transitive private dependency
`christianjbrown/key-value-store`:

```json
"require": { "christianjbrown/christianbrown-database-orm": "dev-main" },
"repositories": [
    { "type": "github", "url": "git@github.com:christianjbrown/christianbrown-database-orm-php.git" },
    { "type": "github", "url": "git@github.com:christianjbrown/key-value-store-php.git" }
]
```

Credentials reach the function as a DSN environment variable, e.g.

```
mysql://christianbrown:<password>@localhost/christianbrown?unix_socket=/tmp/cloudsql/shared-data-services:europe-west2:database&driver=pdo_mysql
```

## Schema management

Schema is managed here with the Doctrine schema tool via the root `doctrine` console entry point.

**The `christianbrown` schema is shared with other personal projects, so never run
`orm:schema-tool:update --complete --force` against it** — that would drop tables this package does not
model. Use `composer db-update-dry-run` (`--dump-sql`, additive) to review the DDL, then apply it
out-of-band (Cloud SQL client over the proxy), the same way `refresh_tokens` is managed.
