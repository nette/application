# Persistent parameters & global state

Persistent parameters survive across requests by riding in every generated URL.
The mechanism spans the whole component tree and has a strict validation contract.

## Declaration and validation

`#[Persistent]` on a **public, non-static** property is discovered by
`ComponentReflection::getParameters()`, which records its type and — for a
presenter — a `since` (the declaring class, used to scope which links may carry it).

- **`loadState()`** converts each incoming param via
  `ParameterConverter::convertType`; a **failure calls `error()` → 404** (a bad URL
  value is client error, not a crash). Conversion is **lossless** for scalars (it
  compares the round-tripped string), and **`callable` is deliberately rejected**
  ("for security reasons"). Null params are ignored (the default stands).
- **`saveState()`/`saveStatePartial()`** re-convert on the way out; a failure here
  throws `InvalidLinkException` (link generation, not a request), and a value equal
  to its default is dropped (`null`) so it does not bloat the URL.

## `#[Parameter]` rides the input path only

`ComponentReflection::getParameters()` returns both `#[Persistent]` and
`#[Parameter]` properties, so `loadState()` fills and type-validates **both** the
same way (a bad value → `error()` → 404). The asymmetry is on the way out: only
persistent entries carry a `since` key, and `getPersistentParams()` filters on its
presence — a `#[Parameter]` property is never written back by `saveState` and never
reaches a URL.

## Global state is aggregated across the whole tree

`getGlobalState()` builds the full persistent-state map that links carry:

1. unconsumed URL params in `globalParams` (prefixed by `id . NameSeparator`);
2. the presenter's own `saveStatePartial`;
3. a walk of the **entire `getComponentTree()`** — each `StatePersistent` component
   contributes `saveState`, prefixed by its `getUniqueId() . NameSeparator`, while a
   `sinces` table records where each key was declared;
4. when a `forClass` is given, keys whose `since` class is not among the target's
   classes/traits are filtered out — so a link only carries state the destination
   can actually receive.

**`saveGlobalState()` empties `globalParams` (consuming them) and caches the
result.** It runs at the end of `run()` (always, since the terminating
`AbortException` lands after it) and again inside `Component::redirect()` before the
link is built. Global state reaches the URL because `createRequest` does `$args +=
$refPresenter->getGlobalState(...)`. When a component attaches, `popGlobalParameters`
hands it its slice of the URL params so its own `loadState` runs.

## The `_fid` flash key

Flash messages survive a redirect via a session section keyed by a short random id
stored in the `FlashKey` (`_fid`) parameter. `hasFlashSession()` is true only when
`_fid` is a non-empty param **and** the session section `Nette.Application.Flash/<fid>`
exists. Consequently `_fid` is appended to a link **only in `redirect`/`forward`
mode and only when a flash session is already live** — ordinary links, and
`redirectX`, never carry it. `getFlashSession()` lazily generates the id (storing it
into the presenter's params) the first time a flash is written.
