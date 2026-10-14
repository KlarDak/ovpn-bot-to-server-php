# MySQL integration tests

These tests use a real PDOConnector and MySQL, not mocks or SQLite. They are
separate from the unit suite and never load the application's `.env`.

## Configure and run

1. Create a dedicated MySQL 8+ database whose name ends in `_test`, for example
   `ovpn_bot_test`, and a dedicated account with `CREATE TEMPORARY TABLES` on it.
   Do not grant this account access to application databases. MySQL temporary
   tables are session-local and subsequent operations on them need no additional
   table privileges. The tests do not create or delete databases.
2. Copy `.env.integration.example` to `.env.integration.local` in the project root.
3. Fill in `BTS_TEST_DB_*` and set `BTS_RUN_DB_TESTS=1`. A blank password must still
   be explicitly present as `BTS_TEST_DB_PASSWORD=`. This file is ignored by Git.
4. Run from the project root:

```sh
php vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml
```

Without opt-in, tests are skipped. With opt-in, missing/invalid settings, missing
pdo_mysql, connection errors and SQL errors fail the run instead of being hidden
as skips. An existing process environment takes precedence over the local file.
For CI, provide these same variables through the CI secret store and additionally
use `--fail-on-skipped` so a disabled suite cannot produce a successful job.

The unit suite is unchanged:

```sh
php vendor/phpunit/phpunit/phpunit -c phpunit.unit.xml
```

## Isolation and scope

Each test opens a new connection and creates TEMPORARY tables from
`Database/schema.sql`. No persistent table is dropped, truncated, or migrated.
Temporary tables are removed when the connection closes, including after a test
failure. The integration bootstrap must be active: discovering these tests via
the default PHPUnit configuration alone does not enable database access.

Covered: generated IDs, parameter binding, missing rows, ordered collections,
updates/deletes, SQL errors, duplicate keys, insert-without-ID errors, and resource
operations for users, accesses, servers and subscriptions. There are no HTTP/VPN
requests. The fixture does not assert production schema equivalence or migrations.

## Existing issues not changed by this suite

`activator/schema.sql` has missing commas before UNIQUE indexes in servers/subs,
an invalid CHARACTER SET clause on the integer subs.server_id, and no AUTO_INCREMENT
on subs.id. The test fixture uses valid definitions with generated subscription IDs;
the original schema is not modified. Default utf8mb4 collation is used in the fixture.

`Types/Subs.php` assigns a database string directly to a `?DateTimeImmutable`
used_at property. The subscription write test therefore checks persisted used_at
through SQL; loading a used subscription through the DTO remains untested here.
`ServersResource::drop()` passes an enum object as a PDO parameter rather than its
string value. Server drop is not covered by this suite. These require separate
source fixes and regression tests; passing this suite does not resolve them.

Validation during preparation: suite discovery and the disabled/skip path only.
A configured MySQL run is required before claiming these integration tests pass.
