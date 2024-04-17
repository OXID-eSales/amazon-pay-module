# Amazon Pay for OXID

Amazon Pay integration for OXID eShop 7.x.

## Documentation

* Official [German Amazon Pay for OXID documentation](https://docs.oxid-esales.com/modules/amazon-pay/de/3.1/)
* Official [English Amazon Pay for OXID documentation](https://docs.oxid-esales.com/modules/amazon-pay/en/3.1/)

## Branch compatibility

* b-6.1.x module branch is compatible with OXID eShop compilation 7.x.

## Installaton

* See the official documentation under [Installation](https://docs.oxid-esales.com/modules/amazon-pay/en/3.1/installation/installation.html#installation)

## Limitations

Find the lst of limitations in the

* German documentation under [Wann können Sie Amazon Pay nicht anbieten?](https://docs.oxid-esales.com/modules/amazon-pay/de/latest/einfuehrung.html#wann-konnen-sie-amazon-pay-nicht-anbieten)
* English documentation under [When can you not offer Amazon Pay?](https://docs.oxid-esales.com/modules/amazon-pay/en/3.1/introduction.html#when-can-you-not-offer-amazon-pay)

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
    * Use OXID official [vagrant box environment](https://github.com/OXID-eSales/oxvm_eshop).
    * Use OXID official [docker sdk configuration](https://github.com/OXID-eSales/docker-eshop-sdk).
    * Use other preconfigured containers, example: ``image: 'selenium/standalone-chrome-debug:3.141.59'``

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
