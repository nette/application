# Routing (Application layer)

`nette/application` subclasses the `nette/routing` engine to add presenter/action/
module conventions. The heavy lifting (mask parsing, the generation index /
`warmupCache`, nested-list `getConstantParameters` aggregation) lives in the base
package — see `nette/routing`'s internals. What is **here**:

## `Application\Routers\Route` (over the base `Route`)

- **`UIMeta` defaults** — injects `module`/`presenter`/`action` metadata with
  kebab-case `FilterIn`/`FilterOut` (merged into `defaultMeta` before the parent
  constructor). This is where a URL segment like `product-edit` becomes the
  `ProductEdit` presenter.
- **kebab-case inflectors** — `action2path`/`path2action` (camelCase ↔ dashes) and
  `presenter2path`/`path2presenter` (`Foo:Bar` ↔ `foo.bar`, `:` ↔ `.`).
- **Closure metadata → `Nette:Micro`** — a `\Closure` target is rewritten to
  `['presenter' => 'Nette:Micro', 'callback' => $closure]`. This is the closure-route
  mechanism, and the reason `Nette:Micro` must stay reachable (below).
- **`<module>` handling** — after a base match, a `module` mask parameter is
  prepended to the presenter (`module:Presenter`) and unset; `constructUrl` splits it
  back out; `getConstantParameters` re-joins `module:presenter`.

## `RouteList::completeParameters` and module scoping

The Application `RouteList` adds module scoping on match: `completeParameters`
**prepends the list's `module` to the matched presenter — unless it starts with
`Nette:`**. `constructUrl` is the mirror: with a `module` set, the target presenter
must start with that prefix (else `null` — not this list's route), and the prefix
is stripped before delegating down. `withModule($m)` creates a nested list whose
`module` is `$m.':'` (a whole group under a module), which is distinct from the
per-route `<module>` mask parameter above. `warmupCache` is *invoked* from `RoutingExtension::afterCompile`
(only when `routing: cache`), but is defined in the base package.

## The `Nette:Micro` firewall exception

`Application::createInitialRequest` **rejects any incoming request to a `Nette:*`
presenter except `Nette:Micro`**: `str_starts_with($presenter, 'Nette:') &&
$presenter !== 'Nette:Micro'` → `BadRequestException`. Framework-internal presenters
must not be directly reachable from the network, but `Nette:Micro` is the legitimate
target that closure routes produce (mapped to `NetteModule\MicroPresenter`), so it
is the single allowed exception. `completeParameters`' `Nette:`-prefix skip exists
for the same reason — module scoping must not be applied to it.

## Request loop and error routing

`processRequest` has a `maxLoop` (default 20) guard, rejects direct requests **to**
the error presenters (only a forward may reach them), and turns an
`InvalidPresenterException` into a `BadRequestException` only on the first request.
`createErrorRequest` routes a `BadRequestException` to `error4xxPresenter ??
errorPresenter` and anything else to `errorPresenter` (5xx); `sendHttpCode` maps a
`BadRequestException` to its HTTP code (else 404) and everything else to 500.

## `PresenterFactory`

`formatPresenterClass` maps a presenter name to a class through the mapping masks
(the hard default is `App\Presentation\*\**Presenter`; `setMapping` overrides), by
substituting `**`→`Part\Part` and `*`→`Part` per `:`-separated segment. Names are
charset-validated, the class is checked for `IPresenter`/non-abstract, and resolved
classes are cached. Aliases are resolved in the **link** layer (the `@alias`
destination), not in routing.
