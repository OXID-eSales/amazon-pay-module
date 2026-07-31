# Change Log for OXID eSales Amazon Pay

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased] - 2.3.0

### Added

- [0007985](https://bugs.oxid-esales.com/view.php?id=7985): Confirmation mails for refunds and cancellations triggered in the backend. Two new module settings (admin > Amazon Pay > Configuration) decide who is notified, separately per event: "Confirmation mail on refund" (`amazonPayRefundMailRecipient`) and "Confirmation mail on cancellation" (`amazonPayCancelMailRecipient`), each with `0` no mail (default), `1` customer, `2` shop owner, `3` both. Defaults are `0`, so updating the module does not start sending mail to existing customers unannounced. The refund mail is sent at the point where Amazon confirmed the refund (`Core\AmazonService::createRefund()`), which covers the refund button in the order view (`Controller\Admin\OrderOverview::refundpayment()`) and removing or cancelling a single order position (`Controller\Admin\OrderArticle::refundAmazon()`); it names order number, refunded amount and order total. The cancellation mail is sent by `Controller\Admin\OrderList::cancelOrder()` after the order was cancelled, whether or not money was refunded — if the cancellation did refund money, that mail states the refunded amount and the refund mail is suppressed, so the customer receives one mail instead of two. New `Core\Email` (chain extension, four templates under `views/email/{html,plain}/`) and `Core\RefundMailService`, which is the only place deciding whether and to whom a mail goes out; mail or logging failures are caught there, because the refund or cancellation has already happened and must not surface as an error page. Rendering switches the admin mode off and back on, because these mails are triggered from the backend but use frontend templates and frontend language files - without that, core idents such as `ORDER_NUMBER` render as "ERROR: Translation for ORDER_NUMBER not found!" in the customer's mail. Both classes are deliberately kept free of trigger logic so they can move to the central payment base module later.
- `Core\AmazonService::createRefund()` now returns `true` once Amazon confirmed the refund. It previously had no return statement on the success path, so success was indistinguishable from failure (both `null`); `Controller\Admin\OrderList::cancelOrder()` needs that signal to decide whether its cancellation mail may name a refunded amount. The other return values are unchanged: a string is still the error message of a failed API request, `null` still means no refund happened. The empty `return;` in the amount-validation branch was made an explicit `return null;` (same behaviour) so the documented return type holds.

- [0007853](https://bugs.oxid-esales.com/view.php?id=7853): New module setting "Sign in via the Amazon email address" (`amazonPayLoginByEMail`, admin > Amazon Pay > Configuration). Until now every Amazon flow ended in `AMAZON_PAY_USEREXISTS` when the email address Amazon returned already belonged to a shop account: the guest user could not be created, the customer was sent back to `cl=user` and asked to sign in with the shop password first. For guest accounts (no password, e.g. created by an earlier express order) that was a dead end, because there is no password those customers could sign in with. The setting decides what happens now and offers three modes — `0` disabled (default, previous behaviour), `1` sign in guest accounts without a password only, `2` sign in all customer accounts including password-protected ones. It applies to both entry points that create the user: the Amazon sign-in button (`Controller\DispatchController`, `action=signin`, buyer response of `getBuyer()`) and the express checkout (`Controller\OrderController::initAmazonPayExpress()`, checkout session response of `getCheckoutSession()`). The sign-in itself happens in the new `Component\UserComponent::loginAmazonCustomer()`, which is called before the guest creation and, if it signs a customer in, lets the flow continue on the "customer is already logged in" branch (Amazon delivery address, express payment method).
- Security properties of the new sign-in path, which is why feature gate and sign-in effect live in the same method: the email address is only taken from a response fetched from the Amazon API in the same request, never from a request parameter; no session state (an active amazon payment) is used as an authentication criterion; the shop password path (`User::login()`/`User::onLogin()`) is not touched at all and no model is overridden for it; the account lookup is restricted to `oxrights = 'user'`, `oxactive = 1` and the current `oxshopid`, so administrator accounts and accounts of other subshops are never signed in; customers in `oxidblocked` are refused; the session challenge (stoken) is required, exactly like in the guest creation path (`UserComponent::createUser()`) this runs in front of; the session id is regenerated as the shop login does; every sign-in without a password entry is logged with the account id and the active mode, independently of the module's debug logging setting.

### Changed

- `Component\UserComponent`: new `getEMailFromAmazonResponse()` reads the buyer email address from both Amazon response shapes and returns an empty string instead of failing when the response carries none; `_getEMailFromAmazonResponse()` is kept as a deprecated delegate
- Admin tooltip of "deactivate Amazon Social Login" no longer claims that a sign-in is only possible when no shop account uses the same email address; it now points to the new setting

## [2.2.1] - 2026-06-11

### FIX

- Use virtual `OxidEsales\Eshop` namespace instead of `OxidEsales\EshopCommunity` in `UserComponent`, `Controller\Admin\OrderList` (`@mixin`) and `Core\AmazonService` (`FieldAlias`), so that edition swaps and module overrides resolve correctly
- Rename `composer.json` key `conflicts` to `conflict` so the constraint blocking OXID eShop `<6.3 | ^7.0` is actually enforced (Composer silently ignores the plural form)
- Remove unused `use` statements in `Controller\Admin\ConfigController` and `Tests\Integration\Controller\Admin\ConfigControllerTest`
- `Controller\OrderController` and `Core\AmazonService`: replace `var_dump()` with `print_r(..., true)` in log message concatenation; `var_dump` returns void, so the dumped payload was never actually included in the log line
- `Core\Payload::setAddressDetails` and `setAddressDetailsFromDeliveryAddress`: default `$addressLine2` to `''` in the else branch where no company is set, so the variable is always defined when the address array is built
- `Model\Order::delete`: replace bare `return;` in the `InputException` catch branch with `return false;` so the method always honours its `bool` return type declaration

### Changed

- Pull `oxid-esales/testing-library` from `dev-b-6.5.x` instead of `dev-b-6.3.x` so phpmd can be upgraded to a PHP 8 compatible parser; phpmd 2.8.1 (pinned via pdepend 2.6.0 on the old testing-library branch) silently aborted on every source file under PHP 8.1
- Bump `phpmd/phpmd` constraint to `^2.11` and add `minimum-stability: dev` / `prefer-stable: true` to satisfy the new testing-library transitive constraints (PHP 7.4 stays supported)
- Regenerate `tests/PhpStan/phpstan-baseline.neon` against the current sources; the previous baseline still ignored patterns that no longer match after the namespace and import cleanups
- Regenerate `tests/PhpMd/phpmd.baseline.xml` with the working phpmd parser; picks up the seven additional violations the broken parser had missed (NPath complexity in `OrderController`, class complexity / else expressions in `Payload`, coupling on `Order`)
- Add `@phpstan` to the `static` composer script so a single `composer static` call covers phpcs, phpmd and phpstan

## [2.2.0] - 2026-03-10

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

### FIX

- [0007878](https://bugs.oxid-esales.com/view.php?id=7878): Provide all address fields for payload
- [0007870](https://bugs.oxid-esales.com/view.php?id=7870): Fix tpl-include-error
- [0007893](https://bugs.oxid-esales.com/view.php?id=7893): Fix AmazonPay Default for paymentstrategy 

### NEW

- add debug-logging

## [2.1.8] - 2025-12-05

- [0007831](https://bugs.oxid-esales.com/view.php?id=7831): Fix if I'm logged in and then use the Express button, the delivery address from OXID must be used
- Show a hint, if Amazon-Session is active, if you go back to detailspage or basket during the checkout
- clear Cache before deactivate the module, prevent possible maintenance mode in case of other installed modules

## [2.1.7] - 2025-08-18

- Added `is_string` check for [PhpHelper::jsonToArray()](./src/Core/Helper/PhpHelper.php)
- Fixed wrong namespace on Controller/Admin/OrderArticle 
- Changed [Amazonclient::getCharge()](./src/Core/AmazonClient.php) to match the upstream library call
- [0007715](https://bugs.oxid-esales.com/view.php?id=7715): Fix wrong HTML-Code-Output
- [0007718](https://bugs.oxid-esales.com/view.php?id=7718): Fix compatibility-Issue with Core (Method-Return-Values must be compatible with CORE)
- [0007728](https://bugs.oxid-esales.com/view.php?id=7728): Fix that Items are not added when paying with AmazonPay Express from Minibasket (Flyout)
- [0007752](https://bugs.oxid-esales.com/view.php?id=7728): Fis that Orders are NOT always refunded when cancelled, even if the option for this is deactivated.
- Prevent the shop from breaking on misconfiguration or if the amazon service is down. Thanks to https://github.com/GM-Alex
- show AmazonPay-Express-Buttons only if Payment AmazonPayExpress is active and Show-Option for Button is enabled
- Fixed with the two-step capture, the opportunity to see the money is again in the backend.
- DeliveryCountries only restricted by merchant
- [0007778](https://bugs.oxid-esales.com/view.php?id=7778): onOrderSend in Admin only for AmazonOrders
- [0007791](https://bugs.oxid-esales.com/view.php?id=7791): Fix Call to a member function getActiveCountry() on bool
- [0007790](https://bugs.oxid-esales.com/view.php?id=7790): TypeError Constants::isAmazonPayment(): Argument #1 ($paymentId) must be of type string, null given

## [2.1.6] - 2024-08-15

- [0007654](https://bugs.oxid-esales.com/view.php?id=7654): Fix Capture type is incorrectly evaluated
- [0007636](https://bugs.oxid-esales.com/view.php?id=7636): Fix Refund value can only be entered with a point, semicolon is not possible
- [0007670](https://bugs.oxid-esales.com/view.php?id=7670): Fix Whitepage when writing log (in some cases)
- [0007702](https://bugs.oxid-esales.com/view.php?id=7702): Fix: When config two-Step capture is done, oxorder->oxtransstatus is now set to OK

## [2.1.5] - 2024-01-26

- Bugfix in Basket and AmazonService

## [2.1.4] - 2023-12-05

- [0007538](https://bugs.oxid-esales.com/view.php?id=7538): Amazon Pay - Values are stored correctly in the YAML
- [0007542](https://bugs.oxid-esales.com/view.php?id=7542): Transaction-History in case of a refund is not updated + suggested refund value
- add Templates for overloading JS and CSS Resources (Requirement for integration of CMPs)

## [2.1.3] - 2023-09-07 - Release

### Fixed
* Delivery address was not send to Amazon
* Fixed IPN history list in adminarea
* Change requirement for PHP7.0 compatibility
* In addition to the country restrictions on the payment method, also check the country restrictions of the shop.
* Send orderNr as ReferenceMerchantId to Amazon
* calculate AmazonPayExpress-Deliverycosts based on provided Country from Amazon
* [0007379](https://bugs.oxid-esales.com/view.php?id=7379) Fix Error messages from the DispatchController spam the log
* Do not duplicate IPN and transaction history entries in order backend
* [0007508](https://bugs.oxid-esales.com/view.php?id=7508) now automated refund and cancel are optional
* [0007501](https://bugs.oxid-esales.com/view.php?id=7501) fix to buy variants in OXID
* Show Charge-Status additionally from API in Admin-Order-Overview
* Config-Values still exists after (de-)activating in OXID >=6.3
* add IPN-Handling of declined authorizations

## [2.1.2] - 2023-06-07 - Release

### Fixed

* Compatibility-Issue with Other themes like Flow
* [0007475](https://bugs.oxid-esales.com/view.php?id=7475) Compatibility-Issue with OXID 6.1
* [0007471](https://bugs.oxid-esales.com/view.php?id=7475) Compatibility-Issue with OXID 6.1
* [0007462](https://bugs.oxid-esales.com/view.php?id=7475) if mandatory Confirm General Terms and Conditions field is activated -> you go round in circles

## [2.1.1] - 2023-05-25 - Release

### Fixed

* [0007471](https://bugs.oxid-esales.com/view.php?id=7471) Module throws an exception in case the button Sign off is used during the checkout
* [0007455](https://bugs.oxid-esales.com/view.php?id=7455) if Amazon Pay module is active, then the shipping address is ignored for all payment methods
* [0007462](https://bugs.oxid-esales.com/view.php?id=7462) if mandatory Confirm General Terms and Conditions field is activated -> you go round in circles
* compatibility issues with core or other payment-modules

## [2.1.0] - 2023-03-16 - Release

### Added

* added additional payment button
* If the order or individual items are canceled or deleted, Amazon will issue a refund or cancel
* Added log in via Amazon
* Added partial refund

### Fixed

* Fix bug showing of maintenance mode in admin panel after refund
* [0007462](https://bugs.oxid-esales.com/view.php?id=7462) mandatory Confirm General Terms and Conditions field
* [0007463](https://bugs.oxid-esales.com/view.php?id=7463) reAdd missing MerchantReferenceId
* [0007350](https://bugs.oxid-esales.com/view.php?id=7350) OXTRANSID contains authorize status codes

## [2.0.1] - 2023-02-10 - Release

### Added

* update template to use Amazon Pay Express
* extends functionality of Payload objects
* add methods `ViewConfig::setArticlesId(string)`, `ViewConfig::getPayloadExpress()`, `ViewConfig::getSignature()`

### Fixed

* [0007345](https://bugs.oxid-esales.com/view.php?id=7345) Refunds are not booked
* [0007369](https://bugs.oxid-esales.com/view.php?id=7369) Delay in the response when amazon pay button is clicked
* [0007368](https://bugs.oxid-esales.com/view.php?id=7368) Declined message is not shown when buyer selects the declined simulation code card
* [0007371](https://bugs.oxid-esales.com/view.php?id=7371) Amazon pay button is missing from the product/basket page after the previous order is placed successfully
* [0007370](https://bugs.oxid-esales.com/view.php?id=7370) Emails are not shared to buyer after successful placement of order
* [0007379](https://bugs.oxid-esales.com/view.php?id=7379) Error messages from the DispatchController spam the log, probably a template is missing here
* [0007351](https://bugs.oxid-esales.com/view.php?id=7351) PlatformId set in headers instead of Payload

## [2.0.0] - 2022-08-22 - Release with new Namespace

* we change the namespace from OxidProfessional (oxps) to OxidSolutionCatalysts (osc)
* please read the documentation for the upgrade from v1.2.0 to v2.0.0
* rename method `ViewConfig::isCompatibleTheme()` as  `ViewConfig::isThemeBasedOn()`

## [1.2.0] - Technical Release

* simplify the template structure (remove theme param from metadata, add switch within the templates)
* change folder structure of module fir better testing
* refresh default-amazon-countries
* dont destroy session basket if you failed by clicking the amazon button with an existing user-account
* some session tweaks for better consumer experience during checkout
* us github Actions for testing the module

## [1.1.3] - 2022-05-17 - Maintenance Release

* faster checkout
* Currency restriction may prevent Amazon Payment
* No Country restriction allows delivery in all Amazon Countries
* remove Validation Hack
* use central constante for paymentid and deladr

## [1.1.2] - 2022-05-10 - Backward-Compatibility for OXID 6.1

* Add Backward-Compatibility for OXID 6.1
* Add Secure use of OrderController::execute
* Fallback InvoiceAddress (With the Amazon button (without OXID login) we use the billing address
  from Amazon. However, if this does not correspond to the shop countries, we fall back on the
  Amazon delivery address as the billing address, since the delivery addresses have already been
  restricted by country beforehand)

## [1.1.1] - 2022-04-01 - Maintenance Release

* change handling of required fields

## [1.1.0] - 2022-03-25 - First Release for OXID 6.2

* change the module-id to oxps_amazonpay
* configuration-handling OXID6.2 compatible

## [Undecided] - unreleased

## [1.0.0] - First Release for OXID 6.1
