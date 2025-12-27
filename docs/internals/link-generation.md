# Link generation

`LinkGenerator::createRequest` is the densest, most trap-laden code in
the package. It builds a `Request` from a destination string, in five phases:
(1) signal/component resolution, (2) alias + presenter/action split, (3) signal
argument processing, (4) action argument processing, (5) missing-param check and
assembly.

## The mode strings — and `redirectX` is real

The `$mode` string is **not** just `link`/`redirect`/`forward`. There are **five**:

| mode | URL from `link()`? | flash `_fid`? | notes |
|---|---|---|---|
| `link` | relative (unless `absoluteUrls` or `//` prefix) | no | the normal case |
| `redirect` | absolute | yes* | `Component::redirect` |
| `forward` | — (returns null) | yes* | Request via `$lastRequest`; no `isLinkable` enforcement |
| `test` | — (returns null) | no | `isLinkCurrent`; skips the missing-param check |
| `redirectX` | absolute | no | **canonicalization only** |

`redirectX` is **not a phantom** — it is live in `Presenter::canonicalize()`. It
behaves like a redirect that yields an **absolute URL with no flash key**, so the
result can be compared byte-for-byte against the current request URL. The
`@param` docblock under-documents it (it lists only `forward|redirect|link`); trust
the code. `*`Flash is added only when the mode is `redirect`/`forward` **and** the
presenter has an active flash session — never for `redirectX` or `link`.

## The `'#'` argument is reserved for the fragment

`link()` (not `createRequest`) strips a `'#'` key out of `$args` and appends it as
the URL fragment — rawurlencoded, overriding any `#fragment` in the destination
string (an empty-string value falls back to it); a non-scalar value throws
`InvalidLinkException`. So `'#'` never reaches the `Request`, and no real parameter
may be named `#`. A `?query` inside the destination is deprecated and **replaces**
`$args` entirely (`parseDestination` returns it as `args`).

## `$lastRequest` is an out-of-band return channel

`createRequest` returns a URL, but also stores the built `Request` in the public
`@internal $lastRequest` (reset at the start, set at the end). This is how callers
retrieve the request without it being the return value — `getLastCreatedRequest()`,
the `'current'` flag that `isLinkCurrent` reads (even though `test` mode returns
`null`), and `Application::createErrorRequest` after a forward.

## The 4xx-not-500 contract

A malformed parameter must degrade to 4xx, not crash to 500, but only for
*untrusted* values. The mechanism is a **subtype**:
`InvalidRequestParameterException extends InvalidLinkException`.

- `ParameterConverter::toParameters` throws the **subtype only for supplemental
  values** — those pulled from the *current request's own params* when the
  destination is `this` (`$refPresenter->getParameters()`). Explicit caller-supplied
  args throw the plain parent `InvalidLinkException`.
- **`redirect`/`redirectPermanent`/`forward` catch only the subtype** → `error()`
  (4xx); **`isLinkCurrent` catches the subtype** → `false`.
- **`link`/`canonicalize` catch the parent** `InvalidLinkException` → the subtype
  passes through as before → `#error` / silently abandoned canonical redirect.

The net effect: a hostile `?param=<garbage>` re-fed through `redirect('this')` in
`startup()` yields 4xx, while a programmer's typo in an explicit link argument still
surfaces as an exception. The subtype is BC-safe because it passes existing
`catch (InvalidLinkException)` blocks.

`$invalidLinkMode` (bit flags `Silent`/`Warning`/`Exception`/`Textual`) decides what
`handleInvalidLink` does with an escaped `InvalidLinkException` in the `link` path
(`#`, `E_USER_WARNING`, rethrow, or `#error: <msg>`).

## Two `link()`s

`Presenter` has **no** `link()` of its own — it inherits `Component::link()`, which
delegates to the presenter's `LinkGenerator` in `link` mode and **wraps exceptions
via `handleInvalidLink` (respecting `$invalidLinkMode`)**. The standalone
`LinkGenerator::link()` (constructed with its own router + reference URL) does
**not** swallow exceptions — it always throws — and is what you use to generate
links outside a running presenter. Its default `$mode = null` also means the URL
is always **absolute** (relativization applies only to an explicit `'link'` mode).
