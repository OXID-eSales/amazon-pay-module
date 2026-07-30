# Security Considerations

This document describes known security considerations that have been reviewed and intentionally left unfixed, either because they require architectural changes, have very low practical risk, or are mitigated by other factors.

## Checkout-Session-ID Format Validation

**Files:** `src/Controller/DispatchController.php`, `src/Controller/OrderController.php`
**Risk:** Low

The Amazon Checkout Session ID from the request is stored in the session without format validation. Amazon Session IDs follow a UUID-like format, but any string is currently accepted and passed to the Amazon API. The Amazon API will reject invalid session IDs, providing implicit validation.

**Mitigation:** Invalid session IDs are rejected by Amazon's API, causing a clean error. Adding regex validation would provide defense-in-depth but is not strictly necessary.

## TOCTOU in IPN Processing

**File:** `src/Controller/DispatchController.php`
**Risk:** Low

After successful SNS signature validation of the IPN message, data is re-read from a separate source (`PhpHelper::getPost()`). In theory, this could lead to processing different data than what was validated. In practice, Amazon SNS sends `application/json`, so `$_POST` remains empty and `php://input` is used consistently.

**Mitigation:** The re-read returns the same data because Amazon SNS uses JSON content type. A full fix would require refactoring to pass the validated message object through the processing chain.

## Race Conditions in Order Processing

**Context:** The Amazon Pay result callback and order finalization can overlap.
**Risk:** Low-Medium

There is no explicit database locking (SELECT FOR UPDATE) during order finalization. Concurrent requests (double-click, multiple tabs) could theoretically cause inconsistent state.

**Mitigation:** Amazon Pay uses a synchronous checkout flow (not webhook-based capture), which reduces the window for race conditions compared to other payment modules.

## `|raw` on Amazon Payload and JSON Response (Twig)

**Files:** Multiple Twig templates
**Risk:** Low

`|raw` is used on `aPayload` (Amazon Checkout payload) and `jsonResponse` in Twig templates. These values are generated server-side via `json_encode()`, which inherently escapes HTML special characters in string values. The `|raw` is necessary because `json_encode` output should not be double-escaped.

**Mitigation:** `json_encode()` provides XSS-safe output for HTML contexts. The risk would only materialize if the rendering pipeline were fundamentally changed.

## Sign-in via the Amazon Email Address (`amazonPayLoginByEMail`)

**Files:** `src/Component/UserComponent.php`, `src/Controller/DispatchController.php`, `src/Controller/OrderController.php`
**Risk:** depends on the configured mode, disabled by default

When the merchant enables this setting, a customer is signed into an existing shop account because the Amazon account uses the same email address — without entering the shop password. In mode `2` ("all customer accounts") this couples access to the shop account to the Amazon account: whoever controls an Amazon account with that email address reaches the shop account including addresses and order history, so the shop password alone no longer protects it. Mode `1` ("guest accounts without a password only") does not have that property, because such accounts have no password that could be bypassed. The setting is therefore opt-in and defaults to `0` (off).

The implementation deliberately concentrates the decision in `UserComponent::loginAmazonCustomer()`:

- The email address is only read from a response the module fetched from the Amazon API in the same request (`getBuyer()` / `getCheckoutSession()`), never from a request parameter.
- No session state (for example "an amazon payment is active") is used as an authentication criterion, and no model overrides the shop password path (`User::login()` / `User::onLogin()`).
- The account lookup is restricted to `oxrights = 'user'`, `oxactive = 1` and the current `oxshopid`; customers in `oxidblocked` are refused. Administrator accounts can never be signed in this way.
- The session challenge (stoken) is required, the session id is regenerated, and every sign-in is logged with the account id and the active mode.

**Mitigation:** off by default; the admin tooltip states the consequence of mode `2`; mode `1` covers the dead-end case (guest accounts from earlier express orders) without weakening any password.

---

*Last updated: 2026-07-30*
