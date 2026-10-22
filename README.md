# Amazon Pay for OXID

Amazon Pay integration for OXID eShop 6.0 and above.

## ⚠️ End of life

This branch (`b-6.0.x`, module version 1.x for OXID eShop 6.0) is **no longer maintained**.
Version **1.6.4 is the final release**. There will be no further bug fixes or security updates.

Please update your shop. We strongly recommend OXID eShop 7 together with Amazon Pay 3.x.
If that is not possible right away, update at least to OXID eShop 6.1 - 6.5 and use Amazon Pay 2.x (branch `b-6.3.x`).

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
* b-6.0.x module branch is compatible with OXID eShop compilation 6.0 (end of life, final release 1.6.4)

## Install for OXID

* see Official documentation

## Limitations

List of Limitations could be found in

* german Documentation [Limitations](https://docs.oxid-esales.com/modules/amazon-pay/de/latest/einfuehrung.html#wann-konnen-sie-amazon-pay-nicht-anbieten)
* english Documentation [Limitations](https://docs.oxid-esales.com/modules/amazon-pay/en/latest/einfuehrung.html#wann-konnen-sie-amazon-pay-nicht-anbieten)

## Running tests

Warning: Running tests will reset the shop.

#### Requirements:
* Ensure test_config.yml is configured:
    * ```
    partial_module_paths: osc/amazonpay
    ```
    * ```
    activate_all_modules: true
    run_tests_for_shop: false
    run_tests_for_modules: true
    ```
* For codeception tests to be running, selenium server should be available, several options to solve this:
    * Use OXID official [docker sdk configuration](https://github.com/OXID-eSales/docker-eshop-sdk).
    * Use other preconfigured containers, example: ``image: 'selenium/standalone-chrome-debug:3.141.59'``

#### Run

Running phpunit tests:
```
vendor/bin/runtests
```

Running phpunit tests with coverage reports (report is generated in ``.../amazonpay/Tests/reports/`` directory):
```
XDEBUG_MODE=coverage vendor/bin/runtests-coverage
```

Running codeception tests default way (Host: selenium, browser: chrome):
```
vendor/bin/runtests-codeception
```

Running codeception tests example with specific host/browser/testgroup:
```
SELENIUM_SERVER_HOST=seleniumchrome BROWSER_NAME=chrome vendor/bin/runtests-codeception --group=examplegroup
```
