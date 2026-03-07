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

---

*Last updated: 2026-03-07*
