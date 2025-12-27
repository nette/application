# Application internals

How `nette/application` (the MVC layer) works underneath, for agents editing it.
The emergent models don't share much context, so they're split by seam:

- **[lifecycle.md](lifecycle.md)** — `Presenter::run()`, the exact phase order,
  `AbortException`, signal dispatch, and canonicalization.
- **[link-generation.md](link-generation.md)** — `LinkGenerator::createRequest`,
  the mode strings (including the real `redirectX`), destination parsing,
  `$lastRequest`, and the 4xx-not-500 contract. The densest, trickiest code.
- **[persistent-state.md](persistent-state.md)** — persistent parameters,
  `loadState`/`saveState`, whole-tree global-state aggregation, and the `_fid`
  flash key.
- **[access-control.md](access-control.md)** — `AccessPolicy`, `#[Requires]`, the
  three enforcement sites, and the implicit same-origin (CSRF) rule for signals.
- **[snippets.md](snippets.md)** — AJAX partial rendering: invalidation flags, the
  snippet-mode render, and the payload contract.
- **[routing.md](routing.md)** — the Application `Route`/`RouteList` subclasses,
  the `Nette:Micro` firewall exception, and `PresenterFactory`.
- **[templates.md](templates.md)** — template/layout file lookup and typed
  templates.
