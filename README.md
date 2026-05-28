# Amazon Pay for OXID

Amazon Pay integration for OXID eShop 6.0 and above.

## Documentation

* Official [German Amazon Pay for OXID 6.0 documentation](https://docs.oxid-esales.com/modules/amazon-pay/de/1.6/)
* Official [German Amazon Pay for OXID 6.1 to 6.5 documentation](https://docs.oxid-esales.com/modules/amazon-pay/de/2.1/)
* Official [German Amazon Pay for OXID from 7.0 documentation](https://docs.oxid-esales.com/modules/amazon-pay/de/3.1/)
* Official [English Amazon Pay for OXID 6.0 documentation](https://docs.oxid-esales.com/modules/amazon-pay/en/1.6/)
* Official [English Amazon Pay for OXID 6.1 to 6.5 documentation](https://docs.oxid-esales.com/modules/amazon-pay/en/2.1/)
* Official [English Amazon Pay for OXID from 7.0 documentation](https://docs.oxid-esales.com/modules/amazon-pay/en/3.1/)

## Branch Compatibility

* b-7.0.x module branch is compatible with OXID eShop compilation 7.0, 7.1, 7.2, 7.3, 7.4
* b-6.3.x module branch is compatible with OXID eShop compilation 6.1, 6.2, 6.3, 6.4, 6.5
* b-6.0.x module branch is compatible with OXID eShop compilation 6.0

## Install for OXID

* see Official documentation

## Limitations

List of Limitations could be found in

* german Documentation [Limitations](https://docs.oxid-esales.com/modules/amazon-pay/de/latest/einfuehrung.html#wann-konnen-sie-amazon-pay-nicht-anbieten)
* english Documentation [Limitations](https://docs.oxid-esales.com/modules/amazon-pay/en/latest/einfuehrung.html#wann-konnen-sie-amazon-pay-nicht-anbieten)

## Running tests

### Static analysis (CI and local)

These run without an OXID shop installation. Just check the module out and run:

```
composer install
composer phpcs       # code style
composer phpmd       # mess detection
composer phpstan     # static analysis
composer static      # all of the above
```

The same checks run automatically on every push/pull request via `.github/workflows/development.yml`.

### Integration tests (local only)

The tests under `tests/Integration/` exercise the module against a running OXID eShop
(they use `oxNew()`, the OXID registry, the database, and — for `AmazonClientTest` —
the live Amazon Pay sandbox). They are **not** part of the CI; run them locally against
an installed shop with the module active.

Requirements:

* A working OXID eShop 7.0 installation with this module enabled.
* A `tests/.env` file with valid Amazon Pay sandbox credentials
  (`MODULE_AMAZON_PAY_STORE_ID`, `MODULE_AMAZON_PAY_MERCHANT_ID`, …). See
  `tests/.env.example` for the full list of variables.
* `oxid-esales/testing-library` (already declared in `require-dev`).

Run from the shop root:

```
vendor/bin/phpunit -c vendor/oxid-esales/amazon-pay-module/tests/phpunit.xml \
    --bootstrap=source/bootstrap.php \
    --testsuite=Integration
```

With coverage:

```
XDEBUG_MODE=coverage vendor/bin/phpunit -c vendor/oxid-esales/amazon-pay-module/tests/phpunit.xml \
    --bootstrap=source/bootstrap.php \
    --testsuite=Integration \
    --coverage-html=vendor/oxid-esales/amazon-pay-module/tests/reports/coverage
```

#### Develop javascript
- we are using grunt
- currently grunt is not installed in php container
- so install it on your host system: https://gruntjs.com/getting-started
    - `sudo npm install -g grunt-cli`
    - `cd source/modules/osc/amazonpay/resources`
    - npm install grunt --save-dev
- using: grunt
- `cd source/modules/osc/amazonpay/resources`
- `grunt` # this command compiles the sass => out/src/css/* and the out/src/js/*
