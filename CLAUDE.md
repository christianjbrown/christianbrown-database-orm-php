# CLAUDE.md

Guidance for working in this repository. Match the existing conventions exactly — this codebase is
small, uniform, and highly opinionated, so new code should be indistinguishable from what's here.

## What this is

A small, shared PHP 8.5+ **Doctrine ORM** package for the personal `christianbrown` schema on the
shared Cloud SQL (MySQL) instance. It exists so the entity mapping, the `EntityManager` bootstrap, and
the climate-write logic live in **one place** instead of being copied between the christianbrown Google
Cloud Run functions that share the database (for cost). It is a **library, not an application** —
consumers `require` it and pass a DSN.

It is consumed via a GitHub VCS `repositories` entry (it is **not** on Packagist) as a `dev-main`
package, and it depends on the sibling `christianjbrown/php-key-value-store-lib` for the key-value
mapped superclass that `RefreshToken` extends.

- **`Entity\RefreshToken`** — the rotating OAuth token key-value row (`refresh_tokens`); extends
  `AbstractDatabaseKeyValueStoreEntity` from `php-key-value-store-lib`. Used by
  `php-gcp-function-smartthings-climate` (moved here from that repo).
- **`Entity\SmartThingsClimate`** (`smartthings_climate`) and **`Entity\MetOfficeWeather`**
  (`met_office_weather`) — append-only climate-history rows (`recorded_at`, `temperature`, `humidity`),
  written every time a request reaches each function's origin. A future `historical-climate-data`
  function reads these to return per-hour / per-day min & max over a time range. Both are thin
  `#[ORM\Entity]` subclasses of the `#[ORM\MappedSuperclass]` `AbstractClimateReading`, differing only
  by table name (and each carrying an `idx_recorded_at` index on `recorded_at`).
- **`EntityManagerFactory`** — builds a Doctrine `EntityManager` from a DSN string, with native lazy
  objects enabled and a short (`CONNECT_TIMEOUT_SECONDS`) PDO connect timeout so a database outage
  cannot hang a request.
- **`ClimateMeasurementRecorder`** — the shared write: `persist()` + `flush()` one
  `ClimateReadingInterface`. It deliberately does **not** catch — callers wrap it in their own
  `try/catch` so a database failure never disturbs the function's HTTP response.

## Commands

Binaries install into `bin/` (Composer `bin-dir`), not `vendor/bin/`. Both `bin/` and `vendor/` are
gitignored and Composer-installed, so run `composer install` first (it needs SSH / `COMPOSER_AUTH`
access to the private sibling repos). Being a library, it does **not** commit `composer.lock`.

| Task | Command |
| --- | --- |
| Run tests + coverage (opens HTML report) | `composer test` |
| Run tests, no coverage | `php -d memory_limit=-1 ./bin/phpunit --no-coverage` |
| Run one test | `php -d memory_limit=-1 ./bin/phpunit --filter EntityManagerFactoryTest` |
| Static analysis (PHPStan level max) | `composer stan` |
| Check code style | `composer check-style` |
| Auto-fix code style | `composer fix-style` |
| Check / fix style on git diff only | `composer check-style-diff` / `composer fix-style-diff` |
| Dump additive schema SQL | `composer db-update-dry-run` |

Always run `composer fix-style` first, then `composer check-style`, then `composer stan`, then
`composer test` before finishing. CI (`.github/workflows/ci.yml`) runs the same three gates — style →
PHPStan → PHPUnit-with-coverage — on push/PR to `main`, supplying private-repo credentials via the
`COMPOSER_AUTH` secret.

## Schema management

Schema is managed here with the Doctrine schema tool via the root `doctrine` console entry point (which
reads the `DSN` env var and enables native lazy objects). The `db-update-*` composer scripts source
`.local.env` for the DSN.

**The `christianbrown` schema is shared with other personal projects, so never run
`orm:schema-tool:update --complete --force` against it** — that would drop tables this package does not
model. Use `composer db-update-dry-run` (`--dump-sql`, additive) to review the DDL, then apply it
out-of-band via a Cloud SQL client over the proxy (the same way `refresh_tokens` is managed). There is
deliberately **no** `doctrine/migrations`.

## Conventions (follow all of these)

- `declare(strict_types=1);` on every file, immediately after `<?php`.
- **Every concrete class is `final` and implements a matching `...Interface`** in the same namespace
  (`EntityManagerFactory`/`EntityManagerFactoryInterface`,
  `ClimateMeasurementRecorder`/`ClimateMeasurementRecorderInterface`). The **entities are the
  exception**: `AbstractClimateReading` is `#[ORM\MappedSuperclass]` (abstract), and the concrete
  `#[ORM\Entity]` classes (`SmartThingsClimate`, `MetOfficeWeather`, `RefreshToken`) are deliberately
  **non-final** for Doctrine proxy/lazy hydration — each suppresses the `RequireAbstractOrFinal` sniff
  with a scoped `phpcs:disable`. Do not introduce any other non-final or abstract class.
- **Constants live on the class only when private** (e.g. `EntityManagerFactory::CONNECT_TIMEOUT_SECONDS`);
  public constants go on the interface. No magic literals in method bodies.
- **No constructor property promotion** — declare typed `private` properties and assign them in the
  constructor body. Class members (properties then methods) are ordered **alphabetically**.
- Import functions/classes explicitly (`use PDO;`) and reference them unqualified.
- Entities: attribute mapping only (`#[ORM\Column]`, `Types::*`), a `#[ORM\MappedSuperclass]` base
  holding the shared columns + accessors, thin concrete subclasses setting `#[ORM\Table]` /
  `#[ORM\Index]`. Fluent setters return `self`.

## Testing

The `phpunit.xml` config is strict (`requireCoverageMetadata`, `beStrictAboutCoverageMetadata`,
`failOnRisky`, `failOnWarning`, `restrictNotices`/`restrictWarnings`, path coverage).

- **Keep line, branch, method, class, AND path coverage at 100%.** Run `composer test` and check the
  report before finishing. The empty concrete entities carry no executable lines, so they need no
  dedicated coverage (`AbstractClimateReading` is exercised through them).
- **Every test class needs a `#[CoversClass(...)]` attribute** or the run fails. Use PHPUnit
  **attributes, not annotations**.
- Double collaborators via their interface — `self::createStub(...)` for a return-only double,
  `self::createMock(...)` + `->expects(...)` for a verified call. `EntityManagerFactory` is tested by
  building an in-memory SQLite EntityManager (`sqlite3:///:memory:`).

## Adding a feature

1. Add the class + its matching interface (public constants on the interface). Concrete non-entity
   classes are `final`; new entities follow the mapped-superclass + non-final concrete pattern.
2. If you add or change a mapped column, update the schema out-of-band (see Schema management) — never
   `--complete --force` against the shared schema.
3. Add a matching `#[CoversClass]` test, doubling collaborators.
4. Run `composer fix-style`, then `composer check-style`, then `composer stan`, then `composer test`
   and **confirm the coverage report is 100%** on classes, lines, paths, methods, and branches.
