# Change Log for OXID eSales Amazon Pay

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.6.3] - 2026-06-11

### FIX

- Use virtual `OxidEsales\Eshop` namespace instead of `OxidEsales\EshopCommunity` in `UserComponent`, `Controller\Admin\OrderList` (`@mixin`) and `Core\AmazonService` (`FieldAlias`), so that edition swaps and module overrides resolve correctly
- Rename `composer.json` key `conflicts` to `conflict` so the version constraint is actually enforced (Composer silently ignores the plural form), and flip the constraint to `>=6.3`: the previous `<6.3 | ^7.0` value was copied verbatim from the 6.5 (`b-6.3.x`) branch and declared the module incompatible with the very shop versions this branch targets (6.0/6.1/6.2). The corrected mirror image now blocks installation only on 6.3+ / 7.x / 8.x
- `Controller\OrderController` and `Core\AmazonService`: replace `var_dump()` with `print_r(..., true)` in log message concatenation; `var_dump` returns void, so the dumped payload was never actually included in the log line
- `Core\Payload::setAddressDetails` and `setAddressDetailsFromDeliveryAddress`: default `$addressLine2` to `''` in the else branch where no company is set, so the variable is always defined when the address array is built
- `Model\Order::delete`: replace bare `return;` in the `InputException` catch branch with `return false;` so the method always honours its `bool` return type declaration
- `Core\Config::getCountryList`: skip null entries when iterating the loaded country list so `getId()` is no longer called on null
- `Controller\Admin\OrderOverview::refundpayment`: cast the request parameter to string before passing it to `str_replace`, which only accepts `array|string`
- Remove unused `use` statements (`ModuleSettingNotFountException`, `ContainerExceptionInterface`, `NotFoundExceptionInterface`) and the matching `@throws` annotations from `Controller\Admin\ConfigController::save()`; the method body never raised any of them

### Changed

- Slim `.github/workflows/development.yml` down from the full shop-install / unit / codeception / sonarcloud pipeline to a single `styles` job running `composer phpcs` on PHP 7.0. The removed jobs were either already disabled (`if: false` for phpstan, phpmd, codeception, sonarcloud) or relied on a brittle OXID 6.0 shop install. The previous `dependabot.yml` (GitHub Actions update schedule) was dropped together with the heavyweight workflow and is not restored — re-add it on demand if the slim workflow stays around long enough to need automated action bumps

## [1.6.2] - 2026-03-11

### Security

- Fix SQL injection in LogRepository::deleteLogMessageByOrderId: use prepared statement
- Require session token (stoken) for poll endpoint in DispatchController to prevent unauthenticated order state changes
- Validate redirect URL against Amazon domains in OrderController to prevent open redirect
- Add CSRF protection (stoken) to AmazonCheckoutController::createCheckout
- Add CSRF protection (stoken) to AmazonCheckoutAjaxController (confirmAGB, confirmDPA, confirmSPA)
- Remove weak cryptographic UUID fallback in Config (`md5`/`uniqid`/`mt_rand` dead code)
- Add column whitelist for ORDER BY in LogRepository::findLogMessageForChargePermissionId
- Add depth limit to json_decode calls in PhpHelper to prevent DoS via deeply nested payloads
- Add SECURITY.md documenting known security considerations and intentionally unfixed items

- [0007878](https://bugs.oxid-esales.com/view.php?id=7878): Provide all address fields for payload
- [0007893](https://bugs.oxid-esales.com/view.php?id=7893): Fix AmazonPay Default for paymentstrategy

### NEW

- add debug-logging

## [1.6.1] - 2025-12-05

- [0007831](https://bugs.oxid-esales.com/view.php?id=7831): Fix if I'm logged in and then use the Express button, the delivery address from OXID must be used
- Show a hint, if Amazon-Session is active, if you go back to detailspage or basket during the checkout
- clear Cache before deactivate the module, prevent possible maintenance mode in case of other installed modules

## [1.6.0] - 2025-08-19

* First release for OXID 6.0 based on Version for OXID 6.3 (v2.1.7)
