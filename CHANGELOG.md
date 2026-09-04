# Change Log for OXID eSales Amazon Pay

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased] - 3.3.0

### Added

- [0007985](https://bugs.oxid-esales.com/view.php?id=7985): Confirmation mails for refunds and cancellations triggered in the backend. Two new module settings (admin > Amazon Pay > Configuration) decide who is notified, separately per event: "Confirmation mail on refund" (`amazonPayRefundMailRecipient`) and "Confirmation mail on cancellation" (`amazonPayCancelMailRecipient`), each with `0` no mail (default), `1` customer, `2` shop owner, `3` both. Defaults are `0`, so updating the module does not start sending mail to existing customers unannounced. The refund mail is sent at the point where Amazon confirmed the refund (`Core\AmazonService::createRefund()`), which covers the refund button in the order view (`Controller\Admin\OrderOverview::refundpayment()`) and removing or cancelling a single order position (`Controller\Admin\OrderArticle::refundAmazon()`); it names order number, refunded amount and order total. The cancellation mail is sent by `Controller\Admin\OrderList::cancelOrder()` after the order was cancelled, whether or not money was refunded — if the cancellation did refund money, that mail states the refunded amount and the refund mail is suppressed, so the customer receives one mail instead of two. New `Core\Email` (chain extension) and `Core\RefundMailService`, which is the only place deciding whether and to whom a mail goes out; mail or logging failures are caught there, because the refund or cancellation has already happened and must not surface as an error page. Rendering switches the admin mode off and back on, because these mails are triggered from the backend but use frontend templates and frontend language files - without that, core idents such as `ORDER_NUMBER` render as "ERROR: Translation for ORDER_NUMBER not found!" in the customer's mail. Both classes are deliberately kept free of trigger logic so they can move to the central payment base module later. Templates are shipped for both engines: `views/twig/email/{html,plain}/{refund,cancel}.html.twig` and `views/smarty/email/{html,plain}/{refund,cancel}.tpl`, addressed engine-neutrally as `@osc_amazonpay/email/html/refund` and so on.
- Follow-up on the confirmation mails above, after the first customer feedback: the customer facing texts named the payment provider inside the sentence ("we have issued a refund for you via Amazon Pay." and "Amazon Pay credits the amount to the payment method stored in your Amazon account."). For a provider that covers several payment methods that is too specific to be correct — the amount goes back to the card, bank account or wallet the customer actually paid with, not to "Amazon Pay" as the customer reads it. `AMAZON_PAY_REFUND_MAIL_INTRO` and `AMAZON_PAY_REFUND_MAIL_NOTE` are therefore provider neutral now and word for word identical across all payment modules that offer refunds (PayPal, Amazon Pay, Adyen, Stripe, Unzer), so a shop running more than one of them no longer sends differently worded refund mails: "we have issued a refund for you." / "The refund has been credited to the payment method you originally used. When the amount becomes available depends on your payment method and your bank." `AMAZON_PAY_REFUND_MAIL_NOTE` is rendered by the cancellation mail as well (whenever an amount was refunded along with the cancellation), so that mail follows the same wording without a second ident. The shop owner copy keeps the provider information, but takes it out of the sentence: `AMAZON_PAY_REFUND_MAIL_INTRO_OWNER` now reads "A refund has been issued for the following order (Amazon Pay Payment Provider)." — the mail is read next to the order in the backend, where knowing which provider moved the money is the point. The shop owner subjects (`AMAZON_PAY_REFUND_MAIL_SUBJECT_OWNER`, `AMAZON_PAY_CANCEL_MAIL_SUBJECT_OWNER`) keep their "Amazon Pay:" prefix on purpose: a merchant who runs several payment modules sorts and filters these mails by that prefix, and a trailing parenthesis would not survive a truncated subject line in the inbox list. No code and no template change was needed — the provider names only ever lived in the language files (`translations/de/oscamazonpay_de_lang.php`, `translations/en/oscamazonpay_en_lang.php`); the mail templates address the idents only. The same wording change was made in the OXID 6.5 module `osc/amazonpay`.
- `Core\AmazonService::createRefund()` now returns `true` once Amazon confirmed the refund. It previously had no return statement on the success path, so success was indistinguishable from failure (both `null`); `Controller\Admin\OrderList::cancelOrder()` needs that signal to decide whether its cancellation mail may name a refunded amount. The other return values are unchanged: a string is still the error message of a failed API request, `null` still means no refund happened. The empty `return;` in the amount-validation branch was made an explicit `return null;` (same behaviour) so the documented return type holds.
- `Core\Config`: `getRefundMailRecipient()` / `getCancelMailRecipient()` treat an unknown value as "no mail", and a `ModuleSettingBridge` read that throws (module configuration not installed after an update) as "no mail" as well. `setRefundMailRecipient()` / `setCancelMailRecipient()` added for the admin/test path, validating the value before it is stored.

- [0007853](https://bugs.oxid-esales.com/view.php?id=7853): New module setting "Sign in via the Amazon email address" (`amazonPayLoginByEMail`, admin > Amazon Pay > Configuration). Until now every Amazon flow ended in `AMAZON_PAY_USEREXISTS` when the email address Amazon returned already belonged to a shop account: the guest user could not be created, the customer was sent back to `cl=user` and asked to sign in with the shop password first. For guest accounts (no password, e.g. created by an earlier express order) that was a dead end, because there is no password those customers could sign in with. The setting decides what happens now and offers three modes — `0` disabled (default, previous behaviour), `1` sign in guest accounts without a password only, `2` sign in all customer accounts including password-protected ones. It applies to both entry points that create the user: the Amazon sign-in button (`Controller\DispatchController`, `action=signin`, buyer response of `getBuyer()`) and the express checkout (`Controller\OrderController::initAmazonPayExpress()`, checkout session response of `getCheckoutSession()`). The sign-in itself happens in the new `Component\UserComponent::loginAmazonCustomer()`, which is called before the guest creation and, if it signs a customer in, lets the flow continue on the "customer is already logged in" branch (Amazon delivery address, express payment method). Select added to both admin templates (`views/twig/admin/amazonconfig.html.twig` and `views/smarty/admin/amazonconfig.tpl`).
- Security properties of the new sign-in path, which is why feature gate and sign-in effect live in the same method: the email address is only taken from a response fetched from the Amazon API in the same request, never from a request parameter; no session state (an active amazon payment) is used as an authentication criterion; the shop password path (`User::login()`/`User::onLogin()`) is not touched at all and no model is overridden for it; the account lookup is restricted to `oxrights = 'user'`, `oxactive = 1` and the current `oxshopid`, so administrator accounts and accounts of other subshops are never signed in; customers in `oxidblocked` are refused; the session challenge (stoken) is required, exactly like in the guest creation path (`UserComponent::createUser()`) this runs in front of; the session id is regenerated as the shop login does in `UserComponent::afterLogin()`; every sign-in without a password entry is logged with the account id and the active mode, independently of the module's debug logging setting.
- `Core\Config::getLoginByEMailMode()` treats an unknown value as "off", and a `ModuleSettingBridge` read that throws (module configuration not installed after an update) as "off" as well, so a broken or missing setting can never enable the sign-in. `Core\Config::setLoginByEMailMode()` added for the admin/test path, validating the value before it is stored.

### Changed

- `Component\UserComponent`: new `getEMailFromAmazonResponse()` reads the buyer email address from both Amazon response shapes and returns an empty string instead of failing when the response carries none; `_getEMailFromAmazonResponse()` is kept as a deprecated delegate
- Admin tooltip of "deactivate Amazon Social Login" no longer claims that a sign-in is only possible when no shop account uses the same email address; it now points to the new setting (all four admin language files)

## [3.2.2] - 2026-06-18

### FIX

- [0007849](https://bugs.oxid-esales.com/view.php?id=7849): Move the admin backend CSS includes (`bootstrap`, `amazonpay_backend.min.css`) from the global `admin_twig/headitem.html.twig` Twig chain extension directly into the module's own `admin/amazonconfig.html.twig` template, and remove the headitem extension (bug 7849 / OXDEV-9934). Extending the global admin `headitem.html.twig` bakes the module template path into the compiled parent Twig class; that compiled class survives every cache clear in PHP-FPM worker memory, so deactivating the module while a worker still holds it crashes the next admin render with `Twig\Error\LoaderError: Template "@osc_amazonpay/...headitem.html.twig" is not defined`. Loading the CSS from the module-local config template avoids touching any globally re-rendered admin template. The Smarty block extension is unaffected (runtime `blocks` mechanism, no compile-time path baking) and stays in place.

## [3.2.1] - 2026-06-11

### FIX

- Use virtual `OxidEsales\Eshop` namespace instead of `OxidEsales\EshopCommunity` in `UserComponent`, `Controller\Admin\OrderList` (`@mixin`) and `Core\AmazonService` (`FieldAlias`), so that edition swaps and module overrides resolve correctly
- Rename `composer.json` key `conflicts` to `conflict` so the constraint blocking OXID eShop `< 7.0` is actually enforced (Composer silently ignores the plural form)
- Remove unused `use` statements in `Controller\Admin\ConfigController` and `Tests\Integration\Controller\Admin\ConfigControllerTest`
- `Controller\OrderController` and `Core\AmazonService`: replace `var_dump()` with `print_r(..., true)` in log message concatenation; `var_dump` returns void, so the dumped payload was never actually included in the log line
- `Core\Payload::setAddressDetails` and `setAddressDetailsFromDeliveryAddress`: default `$addressLine2` to `''` in the else branch where no company is set, so the variable is always defined when the address array is built
- `Core\Config::getCountryList`: skip null entries when iterating the loaded country list so `getId()` is no longer called on null
- `Controller\Admin\OrderOverview::refundpayment`: cast the request parameter to string before passing it to `str_replace`, which only accepts `array|string`

### Changed

- Add missing `src` analyse path to the `phpstan` composer script so `composer phpstan` actually runs instead of aborting with "At least one path must be specified"
- Apply PHPCBF fixes (multi-line call style, brace placement, header blocks) and shorten log lines that exceeded 120 characters; `composer phpcs` now passes
- Add `--baseline-file` to the `phpmd` and `phpmd-report` composer scripts and add the missing `phpmd-generate-baseline` script; generate an initial `tests/PhpMd/phpmd.baseline.xml` to freeze the existing 165 violations (static `Registry` access, long variable names, high cyclomatic complexity in `Logger` / `Payload` / `Order`) as accepted technical debt
- Regenerate `tests/PhpStan/phpstan-baseline.neon` against the current sources; the previous baseline still ignored a stale `$merchantReferenceId never read` pattern that no longer reports and made phpstan fail
- Replace the deprecated `checkMissingIterableValueType: false` option in `tests/PhpStan/phpstan.neon` with an `ignoreErrors` entry on the `missingType.iterableValue` identifier

## [3.2.0] - 2026-03-11

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
- [0007893](https://bugs.oxid-esales.com/view.php?id=7893): Fix AmazonPay Default for paymentstrategy
- [0007896](https://bugs.oxid-esales.com/view.php?id=7896): Two Buttons in Apex MiniBasket
- [0007902](https://bugs.oxid-esales.com/view.php?id=7902): Fix wrong used tpl-Block
- add blocks in templates for overloading
- remove pull-right in amazonpay-button-template

### NEW

- add debug-logging

## [3.1.7] - 2025-12-05

- [0007831](https://bugs.oxid-esales.com/view.php?id=7831): Fix if I'm logged in and then use the Express button, the delivery address from OXID must be used
- Show a hint, if Amazon-Session is active, if you go back to detailspage or basket during the checkout
- clear Cache before deactivate the module, prevent possible maintenance mode in case of other installed modules

## [3.1.6] - 2025-08-18

- Added `is_string` check for [PhpHelper::jsonToArray()](./src/Core/Helper/PhpHelper.php)
- Fixed wrong namespace on Controller/Admin/OrderArticle
- Changed [Amazonclient::getCharge()](./src/Core/AmazonClient.php) to match the upstream library call
- [0007654](https://bugs.oxid-esales.com/view.php?id=7654): Fix Capture type is incorrectly evaluated
- [0007636](https://bugs.oxid-esales.com/view.php?id=7636): Fix Refund value can only be entered with a point, semicolon is not possible
- [0007685](https://bugs.oxid-esales.com/view.php?id=7685): Smarty-Template Improvements
- Prevent the shop from breaking on misconfiguration or if the amazon service is down. Thanks to https://github.com/GM-Alex
- fix Tpl Paths in Twig
- set Smarty-Tpl-Check-Methods as deprecated
- show AmazonPay-Express-Buttons only if Payment AmazonPayExpress is active and Show-Option for Button is enabled
- Fixed with the two-step capture, the opportunity to see the money is again in the backend.
- DeliveryCountries only restricted by merchant
- [0007778](https://bugs.oxid-esales.com/view.php?id=7778): onOrderSend in Admin only for AmazonOrders
- [0007791](https://bugs.oxid-esales.com/view.php?id=7791): Fix Call to a member function getActiveCountry() on bool
- [0007790](https://bugs.oxid-esales.com/view.php?id=7790): TypeError Constants::isAmazonPayment(): Argument #1 ($paymentId) must be of type string, null given

## [3.1.5] - 2024-03-22

### NEW
* split Version for OXID7

### Fixed
* Bugfix in Basket and AmazonService

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
