# Access control & CSRF

`AccessPolicy` (internal) evaluates `#[Requires]` attributes. The attribute is
repeatable, class- or method-level, and matched with `IS_INSTANCEOF`, so subclasses
(like the deprecated `#[CrossOrigin]`) count as `Requires` instances.

## Enforcement runs at three sites

No single file shows this; the policy is applied wherever user code is about to run:

1. **Class-level** — `Presenter::run()`, in the checkRequirements phase, on the
   presenter's own reflection (before `startup`).
2. **Method-level** — `Component::tryCall()`, i.e. on every `action*`, `render*`
   and `handle*` invocation, right before argument conversion.
3. **Factory methods** — `Component::createComponent()` checks the policy on the
   `createComponent<Name>()` method, so a component can be guarded at creation.

At the first two sites the (older, unrelated) `checkRequirements()` user hook is
also called with the same reflection — the two mechanisms coexist. The factory
site runs only the attribute policy.

## `#[Requires]` semantics

- `methods:` — allowed HTTP methods; `['*']` is a wildcard. **Trap:** a
  *class-level* `methods:` empties `$presenter->allowedMethods` to bypass the
  deprecated legacy `Presenter::checkHttpMethod()`, so the attribute fully replaces
  it. Violation → 405 with an `Allow` header.
- `actions:` — method-level use is allowed **only in presenters**
  (`LogicException` otherwise). Violation → 4xx via `error()`.
- `forward: true` — the element is reachable only via forward; it also makes the
  element **non-linkable**: `AccessPolicy::isLinkable()` is what
  `LinkGenerator::validateLinkTarget` consults for every mode except `forward`
  (see link-generation.md). Violation at runtime → 4xx.
- `ajax: true` — non-AJAX request → 403.
- `sameOrigin: true` — see below.

## The implicit signal CSRF rule

**Every `handle*` method implicitly gets `Requires(sameOrigin: true)`** — added by
`AccessPolicy::applyInternalRules` — unless the method opts out via
`#[Requires(sameOrigin: false)]`, the deprecated `#[CrossOrigin]`, or a legacy
`@crossOrigin` annotation. Same-origin is verified from the **`Sec-Fetch-Site`
header** (`$httpRequest->isFrom(FetchSite::SameOrigin)`), not from a SameSite
cookie.

A violation does **not** produce an error: it calls `Presenter::detectedCsrf()`,
whose default is `redirect('this')` — **the signal is silently dropped** and the
page reloads without it. `detectedCsrf()` is public and overridable.

## `UI\Form` has its own, parallel check

`Form::signalReceived('submit')` does **not** go through `AccessPolicy` for CSRF:
it re-checks `Sec-Fetch-Site` itself, gated by the `$crossOrigin` flag from
nette/forms (`allowCrossOrigin()`), and calls the same `detectedCsrf()`. Two more
suppressions live here: a request with the `RESTORED` flag (set by
`Presenter::restoreRequest` for re-sent POSTs) skips `fireEvents()` entirely, and
`receiveHttpData()` returns null for forwarded requests — a form never submits
across a forward.
