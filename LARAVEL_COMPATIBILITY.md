# Laravel compatibility

Supports the Laravel 11+ authentication contracts without allowing Laravel to rewrite WordPress passwords. Declares the password service property for PHP 8.4.

Focused compatibility checks were run against Laravel 10.48.22 and 13.32.0 on PHP 8.4.23. They use in-memory fixtures/mocked HTTP responses, never live accounts or a WordPress database.

```sh
php tests/compatibility.php /absolute/path/to/application/vendor/autoload.php
```

The supplied Composer autoloader must resolve this checkout and the intended framework/dependency versions. The command exits nonzero on failure. These focused checks supplement the existing test suite; they are not a claim that the full legacy suite was ported to a newer PHPUnit version.
