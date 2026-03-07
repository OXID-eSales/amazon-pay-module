# Change Log for OXID eSales Amazon Pay

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.6.2] - 2026-??-??

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
