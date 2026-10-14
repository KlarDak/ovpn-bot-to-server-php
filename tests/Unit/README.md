# Unit tests

From the project root:

```sh
php vendor/phpunit/phpunit/phpunit -c phpunit.unit.xml
```

Requires the installed development dependencies (PHPUnit 12.5, PHP 8.3+).
This configuration discovers only `tests/Unit/*Test.php` and bootstraps only
Composer's autoloader. It does not load `.env`, `tests/test.php`, or the existing
integration tests. Database and HTTP calls use PHPUnit doubles; no MySQL or VPN
service is required. Use the explicit configuration above: the existing default
configuration still includes integration tests.

Assertions cover resource SQL/parameters and result mapping, missing records,
optional filters, updates, status dates, contract selection, access/active request
payloads, subscription links and the HTTP adapter's authorization and errors.
They do not validate SQL against a real database or server protocol compatibility.

Additional suites cover BotToServer resource wiring, environment parsing and
configuration validation, encryption round trips and corruption, JWT claims and
signature/expiry rejection, nested metrics DTOs, resource reads/state changes,
and HTTP response status boundaries. Environment changes are restored after each
test. All keys, tokens and data used by these suites are synthetic.

Coverage is not complete or measured as a percentage. The concrete PDO and Guzzle
drivers, unfinished methods, and some edge cases remain outside these suites.
Manager currently has no implemented behavior to test.
