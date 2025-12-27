# Snippets & AJAX partial rendering

The snippet mechanism is emergent across three files: `Control` (invalidation
flags), the post-lifecycle block in `Presenter::run()` (the re-send), and
`SnippetRuntime` in the Latte bridge (the capture). None of them shows the whole
picture.

## The pipeline

1. During the lifecycle, `redrawControl()` marks a control (key `"\0"`) or a named
   snippet as invalid.
2. After the terminating `AbortException` lands in `run()`, the AJAX block sets
   `payload->state`, and — when the response is a `TextResponse` and something is
   invalid — flips `snippetMode = true` and sends the `TextResponse` **right
   there**. Since `sendTemplate` only stores the response and aborts, this is the
   template's **only** render, and it happens in snippet mode. Then `sendPayload()`
   replaces `$this->response` with a `JsonResponse` of the payload — that is what
   the presenter returns to `Application`.
3. Every compiled template starts with a prolog injected by
   `UIExtension::snippetRenderingPass`: it calls
   `SnippetRuntime::renderSnippets(...)` and **returns early when it rendered** —
   in snippet mode the main template body never runs.
4. `renderSnippets` renders only the invalid snippet blocks (into
   `payload->snippets[<id>]`, captured via output buffering in `enter`/`leave`),
   then `renderChildren` walks the component tree and recursively renders every
   invalid child `Control` with its `snippetMode` on.

## Invariants & traps

- **Invalidation propagates upward:** `isControlInvalid()` with no argument walks
  the whole `Renderable` subtree — an invalid child makes the parent report
  invalid. A named query falls back to the whole-control `"\0"` flag.
- **Rendering a snippet clears its flag:** `SnippetRuntime::enter()` calls
  `redrawControl($name, redraw: false)`. Re-rendering the same snippet twice in one
  request therefore captures it once.
- **Dynamic snippets are valid only inside a static `{snippet}`/`{snippetArea}`**
  (`E_USER_WARNING` otherwise). A `snippetArea` only relays: its own wrapper output
  is discarded, only inner snippets are captured.
- **The payload contract** (what nette/naja-style clients consume): `snippets`,
  `state` (always set on AJAX — the global persistent state, see
  persistent-state.md), and `redirect` — on AJAX, `redirectUrl()` does not send an
  HTTP redirect but puts the URL into `payload->redirect` and sends the payload.
- Snippet element ids are `snippet-<uniqueId>-<name>` (`getSnippetId`).
- `{snippet}`/`{snippetArea}` compile to `enter`/`leave` calls
  (`Nodes/SnippetNode.php`); the driver instance comes from the `snippetDriver`
  provider and exists only when the template has a control.
