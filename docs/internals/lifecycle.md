# Presenter lifecycle

`Presenter::run(Request): Response` is a fixed sequence; the exact ordering is the
non-local knowledge (some steps sit where a reader would not expect).

## Phase order (`run`)

Before the `try`: `initGlobalParameters()` parses the action, the signal, and the
signal receiver, and calls `loadState()`. Then, inside the `try`:

1. **checkRequirements** — access policy, `checkRequirements`, HTTP method check.
2. **startup** — `onStartup`, then `startup()`, then a guard that the child called
   `parent::startup()` (else `InvalidStateException`).
3. **`action<Action>()`** — via `tryCall`. A `SwitchException` here re-enters with a
   changed action **and disables `autoCanonicalize`**.
4. **component autoload** — every id in `globalParams` is `getComponent(…, throw:
   false)`d.
5. **canonicalize** — only if `autoCanonicalize` (see below).
6. **HEAD** — terminates early on a HEAD request.
7. **`handle<Signal>()`** — `processSignal()`. **This runs after the action and
   before rendering**, which is the ordering that surprises people.
8. **beforeRender** → **`onRender`** (a hook that is easy to forget) → **`render<View>()`**
   (again `SwitchException` → `setView` + retry) → **afterRender**.
9. **sendTemplate** — completes and sends the template response.

So the true order is **startup → action → (autoload + canonicalize) → signal →
beforeRender → onRender → render → afterRender → sendTemplate**.

A `SwitchException` escaping anywhere other than the two retry sites above is
turned by `run()` into a `LogicException` ("Switch is only allowed inside
action*() or render*() method").

## Termination is by exception; there is no explicit "frozen" flag

Every terminating method funnels through `sendResponse()` → `terminate()`, which
**throws `AbortException`**: `sendTemplate`, `sendPayload`/`sendJson`,
`redirectUrl`, `forward`. `run()`'s **empty** `catch (AbortException)` then lets
control fall through to the post-lifecycle steps. So there is no separate "state is
frozen" mechanism — the response method simply throws, and nothing after it in the
lifecycle runs. (`error()` is different: it throws `BadRequestException`, which is
**not** caught in `run()` and bubbles up to `Application`.)

**Post-lifecycle, always runs** (even after redirect/JSON, because the abort lands
here): `saveGlobalState()`, then the AJAX payload block (`payload->state =
getGlobalState()`, snippet mode for an invalidated `TextResponse`), flash-session
30-second expiration, a `VoidResponse` fallback, `onShutdown`, `shutdown()`, and
`return $this->response`.

## The template file is resolved lazily, at `sendTemplate`

`completeTemplate()` copies the presenter's `#[TemplateVariable]` properties into
the template (see templates.md) and, **only if the template has no file set**,
calls `findTemplateFile()` **then** — so template lookup happens at send time, not
when the template object is created. Changing the view is possible right
up to `render<View>`; after `sendTemplate` throws `AbortException` nothing more can
affect it.

## Canonicalization uses the `redirectX` mode

`canonicalize()` (auto-called at step 5 unless `autoCanonicalize` was turned off by
an action switch) returns immediately for AJAX or non-GET/HEAD. Otherwise it builds
the canonical URL with `link(..., 'redirectX')` (see link-generation.md — an
absolute URL with no flash key, used purely for comparison), swallows any
`InvalidLinkException`, and if the current URL differs, redirects — **302 when the
`VARYING` request flag is set, else 301**. That flag is set by the router (external
`nette/routing`); this package only reads it.

## Signals

`initGlobalParameters` reads the signal from `_do` (POST) / `do` (the `SignalKey`),
splitting `{receiver}-{signal}` at the **last** `-` (so a receiver id may itself
contain dashes). `processSignal()` resolves the receiver (`''` = the presenter
itself), raising `BadSignalException` if it is missing or not a `SignalReceiver`,
then calls `signalReceived()` → `handle<Signal>()`. A signal always targets the
current presenter/action; the URL carries it as `?do=<receiver>-<signal>`.
