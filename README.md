# Amazon Pay for OXID

Amazon Pay integration for OXID eShop 6.1 and above.

## Documentation

* official german Amazon Pay checkout for OXID [Documentation](https://docs.oxid-esales.com/modules/amazon-pay/de/latest/)
* official english Amazon Pay checkout for OXID [Documentation](https://docs.oxid-esales.com/modules/amazon-pay/en/latest/)

## Branch Compatibility

* b-6.2.x module branch is compatible with OXID eShop compilation 6.1, 6.2, 6.3 and 6.4

## Install

```bash

# Install desired version of oxid-solution-catalysts/Amazon Pay module
$ composer require oxid-esales/amazon-pay-module
# Run install
$ composer install
# Activate the module
$ ./vendor/bin/oe-console oe:module:install-configuration source/modules/oxps/amazonpay
$ ./vendor/bin/oe-console oe:module:apply-configuration
```

**NOTE:** The location of the oe-console script depends on whether your root package
is the oxideshop_ce (```./bin/oe-console```) or if the shop was installed from
an OXID eShop edition metapackage (```./vendor/bin/oe-console```).

After requiring the module, you need to activate it, either via OXID eShop admin or CLI.

```bash
$ ./vendor/bin/oe-console oe:module:activate oxps_amazonpay
```

## Limitations

List of Limitations could be found in

* german Documentation [Limitations](https://docs.oxid-esales.com/modules/amazon-pay/de/latest/einfuehrung.html#wann-konnen-sie-amazon-pay-nicht-anbieten)
* english Documentation [Limitations](https://docs.oxid-esales.com/modules/amazon-pay/en/latest/einfuehrung.html#wann-konnen-sie-amazon-pay-nicht-anbieten)

## Running tests

Warning: Running tests will reset the shop.

#### Requirements:
* Ensure test_config.yml is configured:
    * ```
    partial_module_paths: oxps/amazonpay
    ```
    * ```
    activate_all_modules: true
    run_tests_for_shop: false
    run_tests_for_modules: true
    ```
* For codeception tests to be running, selenium server should be available, several options to solve this:
    * Use OXID official [vagrant box environment](https://github.com/OXID-eSales/oxvm_eshop).
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