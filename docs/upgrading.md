# Upgrading nette/application

## 3.3.0

- **Removed** `Nette\Bridges\ApplicationLatte\UIMacros`: Latte 2 is no longer supported, the tags are provided by `Nette\Bridges\ApplicationLatte\UIExtension` under Latte 3 (92532c24)
- **Removed** `{ifCurrent}` (deprecated since 3.1.6): use `{if isLinkCurrent('destination')}` (d75c3057)
- **Renamed** `Nette\Application\UI\PresenterComponent` → `Nette\Application\UI\Component`; the old name was removed (3432ff34)
- **Renamed** `Nette\Application\UI\PresenterComponentReflection` → `Nette\Application\UI\ComponentReflection`; the old name was removed (3432ff34)
- **Renamed** `Nette\Application\IRouter` → `Nette\Routing\Router` (alias since 3.1.0); the old name was removed (3432ff34)
- **Renamed** `@persistent` → `#[Nette\Application\Attributes\Persistent]`; using the old name triggers E_USER_DEPRECATED (2b611739)
- **Renamed** `@deprecated` → `#[Nette\Application\Attributes\Deprecated]`; using the old name triggers E_USER_DEPRECATED (2b611739)
- **Replaced** `@crossOrigin` with `#[Nette\Application\Attributes\Requires(sameOrigin: false)]`: using the annotation triggers E_USER_DEPRECATED (2b611739)
- **Replaced** `Nette\Application\Routers\RouteList::offsetSet()` with `Nette\Application\Routers\RouteList::addRoute()` for a route and `Nette\Application\Routers\RouteList::add()` for any other router: appending via `$routeList[] =` triggers E_USER_DEPRECATED; replacing the router at an index via `$routeList[$index] =` has no public replacement, `Nette\Routing\RouteList::modify()` being protected, so add the routers in the order they are to be matched (82c16085)
- **Replaced** `Nette\Application\Routers\RouteList::offsetGet()` with `Nette\Application\Routers\RouteList::getRouters()`: reading via `$routeList[$index]` triggers E_USER_DEPRECATED (82c16085)
- **Deprecated** `Nette\Application\Routers\RouteList::offsetUnset()`: `unset($routeList[$index])` triggers E_USER_DEPRECATED and has no public replacement, `Nette\Routing\RouteList::modify()`, which the deprecation notice names, being protected; leave the router out when the list is built, or call `modify($index, null)` from a subclass (82c16085)
- **Deprecated** `Nette\Application\Routers\RouteList::offsetExists()`: check `Nette\Application\Routers\RouteList::getRouters()` instead; using the old name triggers E_USER_DEPRECATED (82c16085)
- **Deprecated** `Nette\Application\UI\Component::$presenter`: use `Nette\Application\UI\Component::getPresenter()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\UI\Component::$linkCurrent`: use `Nette\Application\UI\Component::isLinkCurrent()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\UI\Presenter::$action`: use `Nette\Application\UI\Presenter::getAction()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\UI\Presenter::$view`: use `Nette\Application\UI\Presenter::getView()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\UI\Presenter::$layout`: use `Nette\Application\UI\Presenter::getLayout()` and `Nette\Application\UI\Presenter::setLayout()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\UI\Presenter::$request`: use `Nette\Application\UI\Presenter::getRequest()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\UI\Presenter::$session`: use `Nette\Application\UI\Presenter::getSession()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\Request::$method`: use `Nette\Application\Request::getMethod()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\Request::$presenterName`: use `Nette\Application\Request::getPresenterName()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\Request::$parameters`: use `Nette\Application\Request::getParameters()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\Request::$post`: use `Nette\Application\Request::getPost()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `Nette\Application\Request::$files`: use `Nette\Application\Request::getFiles()`; using the old name triggers E_USER_DEPRECATED (69bca2f1)
- **Deprecated** `$default` of `Nette\Application\UI\Component::getParameter()`: use `getParameter($name) ?? $default`; passing it triggers E_USER_DEPRECATED (6a10e27a)
- **Deprecated** a query string in a link destination, e.g. `link('Presenter:action?foo=bar')`: pass the parameters as arguments, e.g. `link('Presenter:action', ['foo' => 'bar'])`; using the old form triggers E_USER_DEPRECATED (b6da1870)
- **Deprecated** `Nette\Application\UI\Control::templatePrepareFilters()` (silently deprecated since 3.1.8): it is marked `#[\Deprecated]`, so using it triggers a deprecation notice on PHP 8.4+ (5555cd9d)
- **Deprecated** `Nette\Application\UI\Form::disableSameSiteProtection()` (silently deprecated since 3.0.8): use `#[Nette\Application\Attributes\Requires(sameOrigin: false)]`; it is marked `#[\Deprecated]`, so using it triggers a deprecation notice on PHP 8.4+ (5555cd9d)
- **Deprecated** `Nette\Application\UI\Presenter::createRequest()` (deprecated since 3.2.3): use `Nette\Application\LinkGenerator`; it is marked `#[\Deprecated]`, so using it triggers a deprecation notice on PHP 8.4+ (5555cd9d)
- **Deprecated** `Nette\Application\UI\Presenter::parseDestination()` (deprecated since 3.2.3): use `Nette\Application\LinkGenerator::parseDestination()`; it is marked `#[\Deprecated]`, so using it triggers a deprecation notice on PHP 8.4+ (5555cd9d)
- **Deprecated** `Nette\Application\UI\Presenter::requestToUrl()` (deprecated since 3.2.3): use `Nette\Application\LinkGenerator::requestToUrl()`; it is marked `#[\Deprecated]`, so using it triggers a deprecation notice on PHP 8.4+ (5555cd9d)
- **Signature** `Nette\Application\Application::processRequest()` returns `Nette\Application\Response` instead of `void` and no longer sends it; the caller must call `send()` on the returned response (e0192d23)
- **Signature** `Nette\Bridges\ApplicationLatte\LatteFactory::create()` takes `?Nette\Application\UI\Control $control = null`; a custom implementation must accept it and pass it to `Nette\Bridges\ApplicationLatte\UIExtension` (bed96a1c)
- **Signature** `Nette\Application\UI\Presenter::getRequest()` returns `Nette\Application\Request` instead of `?Nette\Application\Request` and throws `Nette\InvalidStateException` when the presenter is not running (35c9c679)
- **Signature** `$signal` of `Nette\Application\UI\Presenter::isSignalReceiver()` is `string|true|null`; passing `false` now throws a `TypeError` (230381ee)
- **Signature** `$name` of `Nette\Bridges\ApplicationLatte\Template::addFilter()` is no longer nullable (fe26ff9b)
- **Signature** `$cacheStorage` of `Nette\Bridges\ApplicationLatte\TemplateFactory::__construct()` was removed; the Latte cache is set up by the caching extension instead (92532c24)
- **Changed** the default presenter mapping is `App\Presentation\*\**Presenter`; previously `*Module\*Presenter`; set `application.mapping` explicitly to keep the previous structure (30d82cfc)
- **Changed** `Nette\Application\LinkGenerator::link()` takes the `#` key of the arguments as the URL fragment; previously it was passed on as an ordinary parameter, and a non-scalar value now throws `Nette\Application\UI\InvalidLinkException` (e3dabf20)
- **Changed** `Nette\Bridges\ApplicationLatte\TemplateFactory::createTemplate()` no longer adds `Nette\Bridges\ApplicationLatte\UIExtension` when the Latte factory did not; a custom `Nette\Bridges\ApplicationLatte\LatteFactory` must register it itself (bed96a1c)
- **Changed** a presenter that is not registered as a service in the DI container is no longer created dynamically; in development mode with `touchToRefresh` the page is refreshed, otherwise `Nette\Application\InvalidPresenterException` is thrown (4abfb482)
- **Changed** snippet redrawing recognizes only descendants of `Nette\Application\UI\Control`; a component implementing merely `Nette\Application\UI\Renderable` is no longer redrawn (fe26ff9b)
- **Config** `latte.macros`: removed, register Latte 3 extensions under `latte.extensions` (92532c24)

## 3.2.12

- **Changed** `Nette\Application\UI\Component::redirect()` calls `error()` (4xx) when a value carried over from the current request's own persisted parameters cannot be coerced to the target action's parameter type; previously `Nette\Application\UI\InvalidLinkException` propagated and ended as a 500 (8d393b85)
- **Changed** `Nette\Application\UI\Component::isLinkCurrent()` returns false when a value carried over from the current request's own persisted parameters cannot be coerced to the target action's parameter type; previously the exception propagated (8d393b85)
- **Changed** `Nette\Application\UI\Presenter::forward()` calls `error()` (4xx) when a value carried over from the current request's own persisted parameters cannot be coerced to the target action's parameter type; previously the exception propagated and ended as a 500 (8d393b85)

## 3.2.10

- **Deprecated** `Nette\Application\UI\Component::getPresenterIfExists()`: use `getPresenter(throw: false)`; the old name is silently deprecated (893fdcc7)
- **Deprecated** `Nette\Application\UI\Form::getPresenterIfExists()`: use `getPresenter(throw: false)`; the old name is silently deprecated (893fdcc7)
- **Deprecated** `Nette\Application\UI\Presenter::getPresenterIfExists()`: use `getPresenter(throw: false)`; the old name is silently deprecated (893fdcc7)
- **Signature** the `$throw` parameter is back in `Nette\Application\UI\Component::getPresenter()`, `Nette\Application\UI\Form::getPresenter()` and `Nette\Application\UI\Presenter::getPresenter()` and passing it no longer triggers E_USER_DEPRECATED (893fdcc7)
- **Signature** `$contentType` of `Nette\Application\Responses\FileResponse::__construct()` is `string` with the default `'application/octet-stream'`; passing `null` now throws a `TypeError` (dced88ca)
- **Signature** `$contentType` of `Nette\Application\Responses\JsonResponse::__construct()` is `string` with the default `'application/json'`; passing `null` now throws a `TypeError` (dced88ca)
- **Signature** `NetteModule\MicroPresenter::error()` returns `never` instead of `void` (f3f28851)
- **Changed** `{layout auto}` in a referring template no longer calls `Nette\Application\UI\Presenter::findLayoutTemplateFile()` and resolves to no layout there (cdf59b0a)

## 3.2.9

- **Removed** `Nette\Bridges\ApplicationLatte\Template::blueprint()` (0044598c)
- **Deprecated** `Nette\Application\Attributes\CrossOrigin`: use `#[Nette\Application\Attributes\Requires(sameOrigin: false)]`; the old name is silently deprecated (75fb3f4d)
- **Changed** `Nette\Application\UI\Component::link()` throws `Nette\Application\UI\InvalidLinkException` when the target is marked `#[Nette\Application\Attributes\Requires(forward: true)]`, unless the link is built while forwarding (1b0aa552)

## 3.2.8

- **Changed** `Nette\Application\UI\Presenter::$invalidLinkMode` configured in the DI container is now really applied to presenters created outside it; previously it stayed silent, so warnings or exceptions for invalid links may newly appear (74e5d82f)
- **Changed** `Nette\Application\LinkGenerator::createRequest()` no longer passes parameters marked `#[Nette\Application\Attributes\Parameter]` whose value equals the default (56986e64)

## 3.2.7

- **Changed** `Nette\Bridges\ApplicationLatte\Template` is abstract; instantiate `Nette\Bridges\ApplicationLatte\DefaultTemplate` or another subclass (8f567030)
- **Changed** `Nette\Bridges\ApplicationLatte\TemplateFactory::createTemplate()` silently skips a default variable (user, baseUrl, basePath, flashes, control, presenter) whose type does not match the template property; previously it failed with a `TypeError` (edf6c279)

## 3.2.4

- **Signature** `Nette\Application\UI\Form::beforeRender()` returns `void`; affects subclasses overriding it (c0d041fe)
- **Changed** container compilation throws `Nette\InvalidStateException` when `#[Nette\Application\Attributes\Deprecated]` is used on a method other than action, render or handle (630ebb14)
- **Changed** container compilation throws `Nette\InvalidStateException` when `#[Nette\Application\Attributes\Parameter]` or `#[Nette\Application\Attributes\Persistent]` is used on a property that is not public, non-static and non-readonly (630ebb14)
- **Changed** container compilation throws `Nette\InvalidStateException` when `#[Nette\Application\Attributes\Requires]` is used on a method other than action, render, handle or createComponent (630ebb14)
- **Changed** container compilation throws `Nette\InvalidStateException` when an action, render or signal method of a presenter is not public and non-static (630ebb14)
- **Changed** container compilation throws `Nette\InvalidStateException` when a `createComponent<Name>()` method is private or static (630ebb14)

## 3.2.3

- **Replaced** `Nette\Application\UI\Presenter::createRequest()` with `Nette\Application\LinkGenerator::createRequest()`: link building moved to the `Nette\Application\LinkGenerator` service, reachable via `Nette\Application\UI\Presenter::getLinkGenerator()`, so overriding the presenter method no longer affects generated links (850ab855)
- **Deprecated** `Nette\Application\UI\Presenter::$allowedMethods`: use `#[Nette\Application\Attributes\Requires(methods: [...])]` on the presenter class or on the action; the property is silently deprecated (368ae99f)
- **Deprecated** `Nette\Application\UI\Presenter::checkHttpMethod()`: use `#[Nette\Application\Attributes\Requires(methods: [...])]`; the method is silently deprecated (368ae99f)
- **Signature** `$element` of `Nette\Application\UI\Component::checkRequirements()` is typed `\ReflectionClass|\ReflectionMethod`; affects subclasses overriding it (7af4f067)
- **Signature** `Nette\Application\LinkGenerator::link()` is `link(string $destination, array $args = [], ?Nette\Application\UI\Component $component = null, ?string $mode = null): ?string`; the return type is nullable and the first parameter was renamed from `$dest` to `$destination` (51584954)
- **Signature** `$reflection` of `Nette\Application\UI\Presenter::saveState()` was removed; a second argument is now silently ignored (b644c0d4)
- **Signature** `Nette\Application\Attributes\Parameter` and `Nette\Application\Attributes\Persistent` are final and can no longer be extended (5d5f8d18)
- **Changed** `Nette\Application\UI\Component::checkRequirements()` no longer performs the same-origin check for signals; the check runs before it is called, so an override that does not call the parent no longer bypasses it (7af4f067)
- **Changed** `Nette\Application\UI\Component::redirectPermanent()` no longer saves the global state of subcomponents before redirecting; only `Nette\Application\UI\Component::redirect()` does (bb652c12)
- **Changed** `Nette\Application\LinkGenerator::link()` resolves the target action or render method only when it is callable (public, non-static, non-abstract); previously any existing method matched (b309aa94)
- **Changed** `Nette\Application\UI\Presenter::formatActionMethod()` returns `'actionDefault'`; previously `'actiondefault'` (def38b35)
- **Changed** `Nette\Application\UI\Presenter::formatRenderMethod()` returns `'renderDefault'`; previously `'renderdefault'` (def38b35)
- **Changed** `Nette\Application\UI\Presenter::getPersistentComponents()` returns an empty array by default; names declared by the attribute are merged elsewhere, so an override no longer has to call the parent to keep them (d95272f3)
- **Changed** `{templatePrint}` requires latte/latte 3.0.15 or newer and throws `\LogicException` otherwise (7f094568)
- **Changed** `|translate` used without `Nette\Bridges\ApplicationLatte\Template::setTranslator()` ends with Latte's undefined-filter compile error; previously `Nette\InvalidStateException` at render time (c86bdbef)
- **Changed** `latte.extensions`: the cache and forms extensions are registered before the user-configured ones; previously afterwards, which changes precedence of tags and filters defined under the same name by more than one extension (3a4f4017)
- **Config** `application.catchExceptions`: the value `'4xx'` introduced in 3.2.1 was removed, only `true` and `false` are accepted (56be3de3)

## 3.2.1

- **Replaced** `Nette\Application\Application::processException()` with `Nette\Application\Application::createErrorRequest()`: the new method returns a `Nette\Application\Request` (or null) that the caller must pass to `Nette\Application\Application::processRequest()` and no longer sends the response itself (18a38043)
- **Deprecated** `Nette\Application\Application::$catchExceptions`: exceptions are caught whenever `Nette\Application\Application::$errorPresenter` is set; the property is silently deprecated (133d0e7f)
- **Changed** `Nette\Application\Application::$catchExceptions` defaults to `true`; previously `false` (133d0e7f)
- **Changed** `Nette\Application\UI\Presenter::injectPrimary()` called a second time throws a native `\Error` because the injected properties are readonly; previously `Nette\InvalidStateException` (bb59a51b)
- **Config** `application.catchExceptions` also accepts the string `'4xx'` and its default changed to `'4xx'`; previously `false` (d16847f4)

## 3.2.0

- **Removed** `Nette\Application\UI\Presenter::getContext()` (deprecated since 3.1.0): inject the services you need explicitly (75912403)
- **Removed** `Nette\Application\Request::SECURED` (573cd9b9)
- **Removed** `Nette\Application\Routers\Route::getFlags()` (deprecated since 3.1.0) (75912403)
- **Removed** `Nette\Application\Routers\SimpleRouter::getFlags()` (deprecated since 3.1.0) (75912403)
- **Removed** `Nette\Application\Routers\RouteList::count()` (deprecated since 3.1.0): the class no longer implements `Countable`, count `Nette\Application\Routers\RouteList::getRouters()` instead (75912403)
- **Replaced** `Nette\Application\Routers\RouteList::getIterator()` with `Nette\Application\Routers\RouteList::getRouters()`: the class no longer implements `IteratorAggregate`, iterate over the returned array (75912403)
- **Replaced** `$flags` of `Nette\Application\Routers\Route::__construct()` with `$oneWay` of `Nette\Application\Routers\RouteList::addRoute()` (75912403)
- **Replaced** `$flags` of `Nette\Application\Routers\SimpleRouter::__construct()` with the second argument of `Nette\Application\Routers\RouteList::add()` (75912403)
- **Signature** `Nette\Application\UI\Presenter::injectPrimary()` no longer takes `Nette\DI\Container` and the parameter order is now httpRequest, httpResponse, presenterFactory, router, session, user, templateFactory; affects positional calls (bb8f93c6)
- **Signature** `Nette\Application\UI\Control::createTemplate()` and `Nette\Application\UI\Presenter::createTemplate()` take `?string $class = null`; affects subclasses overriding them (e2c2e74b)
- **Signature** property types were added: `Nette\Application\Application::$catchExceptions` is `bool` with default false, `Nette\Application\UI\Control::$snippetMode` is `bool` with default false and `Nette\Application\UI\Presenter::$invalidLinkMode` is `int` with default 0; assigning `null` throws a `TypeError` and an unset value can no longer be recognized by a null check (244fc48a)
- **Signature** `Nette\Bridges\ApplicationLatte\DefaultTemplate::$presenter` is typed `Nette\Application\IPresenter` instead of `Nette\Application\UI\Presenter` (244fc48a)
- **Signature** the third parameter of `Nette\Application\Routers\RouteList::addRoute()` was renamed from `$flags` to `$oneWay` and typed `int|bool`; affects calls using named arguments (b3d618a0)
- **Changed** the `Nette\SmartObject` trait was removed from `Nette\Application\Application`, `Nette\Application\PresenterFactory`, `Nette\Application\LinkGenerator`, `Nette\Application\UI\Link`, all classes in `Nette\Application\Responses`, `Nette\Application\Routers\CliRouter`, `Nette\Bridges\ApplicationLatte\TemplateFactory`, `Nette\Bridges\ApplicationTracy\RoutingPanel`, `NetteModule\ErrorPresenter` and `NetteModule\MicroPresenter`; reading an undefined property or calling an undefined method raises a native PHP error instead of `Nette\MemberAccessException`, and writing an undefined property is no longer blocked (a472b2e0)
- **Changed** `Nette\Application\UI\Form` whose action was changed by `setAction()` no longer adds the hidden signal field in `beforeRender()`, so the signal submit handler is not triggered for such forms (8d8313a3)
- **Changed** `Nette\Application\Routers\SimpleRouter::__construct()` no longer throws `Nette\DeprecatedException` for a `module` key in `$defaults`; the key is accepted as an ordinary default, use `Nette\Application\Routers\RouteList::withModule()` to scope a module (75912403)
- **Config** `application.catchExceptions` defaults to `false`; previously `null` (244fc48a)
- **Config** `latte.xhtml`: removed, using it throws an exception (75912403)
- **Config** `routing.routeClass`: removed, using it throws an exception (75912403)

## 3.1.15

- **Changed** `Nette\Application\AbortException` extends `\LogicException` instead of `\Exception`; affects code catching it (00da68b6)
- **Changed** `Nette\Application\BadRequestException` extends `\LogicException` instead of `\Exception`; affects code catching it (00da68b6)
- **Changed** `Nette\Application\UI\Component::tryCall()` reports a handler that exists but is not callable through `error()`, i.e. `Nette\Application\BadRequestException` (404); previously `Nette\InvalidStateException`, also in 3.2.4 (dafa250c)

## 3.1.14

- **Replaced** `Nette\Application\Routers\RouteList::match()` with `Nette\Application\Routers\RouteList::completeParameters()`: a subclass that overrode match() to complete the matched parameters now implements `protected function completeParameters(array $params): ?array`, which the parent class calls (7f871545)
- **Changed** `NetteModule\MicroPresenter::run()` autowires only callback parameters typed with a class or interface; a parameter with a builtin type keeps the value coming from the request instead of being overwritten by the container lookup (c634aabe)

## 3.1.13

- **Changed** `Nette\Application\UI\Presenter::run()` rejects an HTTP method not listed in the new `Nette\Application\UI\Presenter::$allowedMethods` (GET, POST, HEAD, PUT, DELETE) with a 405 error; set the property to allow other methods (83f51448)

## 3.1.11

- **Renamed** `Nette\Application\UI\Presenter::INVALID_LINK_SILENT` → `Nette\Application\UI\Presenter::InvalidLinkSilent`; the old name still works as an alias (73391b9e)
- **Renamed** `Nette\Application\UI\Presenter::INVALID_LINK_WARNING` → `Nette\Application\UI\Presenter::InvalidLinkWarning`; the old name still works as an alias (73391b9e)
- **Renamed** `Nette\Application\UI\Presenter::INVALID_LINK_EXCEPTION` → `Nette\Application\UI\Presenter::InvalidLinkException`; the old name still works as an alias (73391b9e)
- **Renamed** `Nette\Application\UI\Presenter::INVALID_LINK_TEXTUAL` → `Nette\Application\UI\Presenter::InvalidLinkTextual`; the old name still works as an alias (73391b9e)
- **Renamed** `Nette\Application\UI\Presenter::PRESENTER_KEY` → `Nette\Application\UI\Presenter::PresenterKey`; the old name still works as an alias (73391b9e)
- **Renamed** `Nette\Application\UI\Presenter::SIGNAL_KEY` → `Nette\Application\UI\Presenter::SignalKey`; the old name still works as an alias (73391b9e)
- **Renamed** `Nette\Application\UI\Presenter::ACTION_KEY` → `Nette\Application\UI\Presenter::ActionKey`; the old name still works as an alias (73391b9e)
- **Renamed** `Nette\Application\UI\Presenter::FLASH_KEY` → `Nette\Application\UI\Presenter::FlashKey`; the old name still works as an alias (73391b9e)
- **Renamed** `Nette\Application\UI\Presenter::DEFAULT_ACTION` → `Nette\Application\UI\Presenter::DefaultAction`; the old name still works as an alias (73391b9e)
- **Changed** an action, render or signal method must be public; a protected one throws `Nette\InvalidStateException` instead of being invoked as it was since 3.1.2 (3137e5df)

## 3.1.10

- **Changed** `Nette\Application\UI\Presenter::detectedCsrf()` converts `Nette\Application\UI\InvalidLinkException` raised for an invalid current link into `Nette\Application\BadRequestException`; previously the InvalidLinkException propagated (f2a72dc8)

## 3.1.8

- **Deprecated** `Nette\Application\UI\Control::templatePrepareFilters()`: the method is silently deprecated (65f01cc8)
- **Signature** `$default` of `Nette\Application\UI\Component::getParameter()` was removed from the signature; a second argument still works through `func_get_arg()` but should no longer be passed (83212fd2)

## 3.1.7

- **Renamed** `Nette\Bridges\ApplicationLatte\TranslatorExtension` → `Latte\Essential\TranslatorExtension`; the old name was removed (a831a22c)

## 3.1.6

- **Replaced** `{ifCurrent destination}` with `{if isLinkCurrent('destination')}`: under Latte 3 the tag triggers E_USER_DEPRECATED (2bfc034d)
- **Changed** a modifier other than `|noescape` in `{control}` is a compile exception under Latte 3; under Latte 2 it only triggered E_USER_DEPRECATED (2bfc034d)
- **Config** `latte.macros` has no effect under Latte 3, register extensions under `latte.extensions` (2bfc034d)

## 3.1.5

- **Signature** `Nette\Application\UI\Component::link()`, `lazyLink()`, `redirect()`, `redirectPermanent()` and `isLinkCurrent()` take `$args = []` again instead of the variadic `...$args` introduced in 3.1.4 (b89b3d6a)

## 3.1.4

- **Replaced** `{templatePrint default}` with `{templatePrint Nette\Bridges\ApplicationLatte\DefaultTemplate}`: the special `default` keyword was removed (1256ecf7)
- **Signature** `Nette\Application\UI\Component::link()`, `lazyLink()`, `redirect()`, `redirectPermanent()` and `isLinkCurrent()` take a variadic `...$args` instead of `$args = []` (a987d588)
- **Signature** `Nette\Application\UI\Presenter::canonicalize()` takes a variadic `...$args` instead of `array $args = []` (ce7df20e)
- **Changed** generating a link to a presenter, an action or a signal handler annotated `@deprecated` triggers E_USER_DEPRECATED when invalidLinkMode is enabled; previously it was silent (4422ae62)
- **Changed** `Nette\Application\UI\Presenter::run()` saves the global state and the AJAX payload state even when the request ends early through `forward()`, `redirect()` or `sendResponse()`; previously only after a normal render, so persistent parameters were not saved on early termination (db6eedc1)

## 3.1.2

- **Changed** a protected action, render or signal method is invoked like a public one; previously it was silently treated as not found (06823f59)
- **Changed** a private action, render or signal method makes `Nette\Application\UI\Component::tryCall()` throw `Nette\InvalidStateException`; previously it was silently treated as not found (06823f59)
- **Changed** `Nette\Application\UI\Control::formatTemplateClass()` strips only a trailing "Control" from the class name and returns a name even for a class not ending with it; previously it also stripped "Presenter" and returned null in that case (0a4aeabc)
- **Changed** `Nette\Application\UI\Presenter::formatTemplateClass()` looks for a per-action template class (`FooBarTemplate` for action bar of `FooPresenter`) before falling back to the default one (55c9d45c, 0a4aeabc)

## 3.1.1

- **Deprecated** `latte.xhtml`: using the key triggers E_USER_DEPRECATED (d6989a2e)
- **Changed** `Nette\Application\UI\Component::createComponent()` triggers E_USER_NOTICE when the created component implements neither `Nette\Application\UI\SignalReceiver` nor `Nette\Application\UI\StatePersistent` (d07e7fb3)
- **Changed** a custom template class (`latte.templateClass` or the constructor argument of `Nette\Bridges\ApplicationLatte\TemplateFactory`) must extend `Nette\Bridges\ApplicationLatte\Template`; merely implementing `Nette\Application\UI\Template` makes the factory throw `Nette\InvalidArgumentException` (4b4f2866)
- **Changed** `{control}` outside HTML text, e.g. inside an attribute, is allowed and the component output is escaped for that context; previously it triggered E_USER_WARNING (6d81a0e4)

## 3.1.0

- **Removed** `Nette\Application\Routers\Route::$styles` (deprecated since 3.0.0) (9a161a29)
- **Removed** the `@User(loggedIn)` annotation on presenter action and render methods (deprecated since 3.0.0): check the user in `Nette\Application\UI\Presenter::checkRequirements()` instead (9a161a29)
- **Removed** `|null` (deprecated since 3.0.0) (9a161a29)
- **Removed** a modifier in `{templatePrint}`: using one now throws a compile exception (09ac4e3e)
- **Renamed** `|url` → `|escapeUrl`; the old name was removed (9a161a29)
- **Renamed** `Nette\Application\IRouter` → `Nette\Routing\Router` (silently deprecated since 3.0.0); the old name still works as an alias (d200f234)
- **Renamed** `Nette\Application\IResponse` → `Nette\Application\Response`; the old name still works as an alias (8ffa8fcc)
- **Renamed** `Nette\Application\UI\IRenderable` → `Nette\Application\UI\Renderable`; the old name still works as an alias (8ffa8fcc)
- **Renamed** `Nette\Application\UI\ISignalReceiver` → `Nette\Application\UI\SignalReceiver`; the old name still works as an alias (8ffa8fcc)
- **Renamed** `Nette\Application\UI\IStatePersistent` → `Nette\Application\UI\StatePersistent`; the old name still works as an alias (8ffa8fcc)
- **Renamed** `Nette\Application\UI\ITemplate` → `Nette\Application\UI\Template`; the old name still works as an alias (8ffa8fcc)
- **Renamed** `Nette\Application\UI\ITemplateFactory` → `Nette\Application\UI\TemplateFactory`; the old name still works as an alias (8ffa8fcc)
- **Renamed** `Nette\Bridges\ApplicationLatte\ILatteFactory` → `Nette\Bridges\ApplicationLatte\LatteFactory`; the old name still works as an alias (8ffa8fcc)
- **Replaced** `Nette\Application\UI\Component::getParam()` (deprecated since 3.0.0) with `Nette\Application\UI\Component::getParameters()` when called without an argument and with `Nette\Application\UI\Component::getParameter()` when called with one (9a161a29)
- **Replaced** the numeric `$code` first argument of `Nette\Application\UI\Component::redirect()` (deprecated since 3.0.0) with `Nette\Application\UI\Component::redirectPermanent()` for 301; other codes have no replacement (9a161a29)
- **Replaced** the `module` key in `$defaults` of `Nette\Application\Routers\SimpleRouter::__construct()` with `Nette\Application\Routers\RouteList::withModule()`: passing it now throws `Nette\DeprecatedException` (9a161a29)
- **Replaced** `|normalize` with `Nette\Utils\Strings::normalize()` applied to the value (9a161a29)
- **Replaced** `|toAscii` with `Nette\Utils\Strings::toAscii()` applied to the value (9a161a29)
- **Replaced** `Nette\Bridges\ApplicationLatte\Template::add()` with `Nette\Bridges\ApplicationLatte\DefaultTemplate::add()`: the method moved to the subclass, extend DefaultTemplate if you need it (34c4a715)
- **Replaced** `Nette\Bridges\ApplicationLatte\Template::setParameters()` with `Nette\Bridges\ApplicationLatte\DefaultTemplate::setParameters()`: the method moved to the subclass (34c4a715)
- **Deprecated** `Nette\Application\UI\Presenter::getContext()`: inject the services you need explicitly; using it triggers E_USER_DEPRECATED (0d479ac0)
- **Deprecated** `$flags` of `Nette\Application\Routers\Route::__construct()`: pass the flag to `Nette\Application\Routers\RouteList::addRoute()`; passing it triggers E_USER_DEPRECATED (de327f3f)
- **Deprecated** `$flags` of `Nette\Application\Routers\SimpleRouter::__construct()`: pass the flag to `Nette\Application\Routers\RouteList::add()`; passing it triggers E_USER_DEPRECATED (de327f3f)
- **Deprecated** `Nette\Application\Routers\Route::getFlags()`: using it triggers E_USER_DEPRECATED (de327f3f)
- **Deprecated** `Nette\Application\Routers\SimpleRouter::getFlags()`: using it triggers E_USER_DEPRECATED (de327f3f)
- **Deprecated** `Nette\Application\Routers\RouteList::count()`: count `Nette\Application\Routers\RouteList::getRouters()`; using it triggers E_USER_DEPRECATED (46a15001)
- **Deprecated** `Nette\Application\Routers\RouteList::getIterator()`: iterate over `Nette\Application\Routers\RouteList::getRouters()`; using it triggers E_USER_DEPRECATED (46a15001)
- **Deprecated** a modifier in `{control}`: using one triggers E_USER_DEPRECATED (09ac4e3e)
- **Deprecated** `{control}` used outside HTML text: using it triggers E_USER_WARNING (6a187d1c)
- **Changed** `Nette\Bridges\ApplicationLatte\TemplateFactory` creates `Nette\Bridges\ApplicationLatte\DefaultTemplate` by default; previously `Nette\Bridges\ApplicationLatte\Template`, which no longer declares the presenter, control, user, baseUrl, basePath and flashes variables (34c4a715)
- **Changed** `Nette\Bridges\ApplicationLatte\Template` has no magic `__get`, `__set`, `__isset` and `__unset` and does not use `Nette\SmartObject`; parameters are ordinary public properties and reading an undefined one raises a PHP warning instead of E_USER_NOTICE (450720cb)
- **Changed** `Nette\Bridges\ApplicationLatte\DefaultTemplate::add()` throws `Nette\InvalidStateException` on a second call for the same name even when the stored value was null; previously such a parameter could be overwritten (450720cb)
- **Changed** `Nette\Bridges\ApplicationLatte\TemplateFactory` sets the default variables only when the template class declares them as properties and the value is not null (981a81d2)
- **Changed** `Nette\Application\UI\Control::createTemplate()` and `Nette\Application\UI\Presenter::createTemplate()` look up a template class by naming convention (`FooControl` to `FooTemplate`) and use it instead of the default one when it exists (6c24aaa7)
- **Changed** a flash message always expires after 30 seconds; previously after 3 seconds unless the response was a redirect (669e02c0)
- **Changed** `Nette\Application\UI\Presenter::run()` always calls `sendTemplate()` regardless of what `getTemplate()` returned; overriding getTemplate() to return null no longer suppresses rendering (74c9111c)
- **Changed** `Nette\Bridges\ApplicationLatte\Template::render()` passes the whole template object to Latte instead of an array of parameters, so the template's public methods become available as Latte functions (c3de3299)
- **Changed** `Nette\Bridges\ApplicationLatte\Template::renderToString()` passes the whole template object to Latte instead of an array of parameters (c3de3299)
- **Config** `application.catchExceptions` is forced to true in production mode regardless of the configured value (2a12b806)
- **Config** `application.scanFilter` is an fnmatch pattern and defaults to `*Presenter`; previously a substring searched in the class name with the default "Presenter" (00dc0177)
- **Config** `routing.routeClass`: deprecated, using it triggers E_USER_DEPRECATED (f8677b06)
- **Config** `routing.router`: the service is created only when at least one route is configured (aeafb720)
- **Config** `routing.router`: when no other service of type `Nette\Routing\Router` exists, a default `Nette\Application\Routers\SimpleRouter` is registered (5c2b5413)

## 3.0.8

- **Renamed** `Nette\Application\UI\Form::disableSameSiteProtection()` → `Nette\Application\UI\Form::allowCrossOrigin()`; the old name is silently deprecated, also in 3.1.2 (72d5945c)

## 3.0.7

- **Config** `application.debugger`: when not set explicitly, the panel is enabled by the presence of a `Tracy\BlueScreen` service; previously by whether tracy/tracy was installed (872c1434)
- **Config** `routing.debugger`: when not set explicitly, the routing panel is enabled by the presence of a `Tracy\Bar` service; previously by whether tracy/tracy was installed (872c1434)

## 3.0.5

- **Changed** unserializing `Nette\Bridges\ApplicationLatte\Template` throws `Nette\NotImplementedException`; previously it succeeded silently (edcd93d6)
- **Changed** `Nette\Application\Request::isMethod()` returns false when the request method is null; previously it threw a `TypeError` under strict types (2974fbfd)

## 3.0.4

- **Changed** `Nette\Application\UI\Presenter::restoreRequest()` redirects via `redirectUrl()` on a GET request; previously it always forwarded the restored request with `Nette\Application\Responses\ForwardResponse` (c84fbf32)

## 3.0.3

- **Signature** `$context`, `$presenterFactory` and `$router` of `Nette\Application\UI\Presenter::injectPrimary()` lost their default null value and must be passed explicitly (edcfc7eb)
- **Changed** `Nette\Application\Responses\FileResponse::__construct()` throws `Nette\Application\BadRequestException` also when the file exists but is not readable; previously only when it did not exist (095b6f2f)
- **Changed** `Nette\Application\Responses\FileResponse::send()` throws `Nette\Application\BadRequestException` when the file cannot be opened; previously it continued with an invalid handle (095b6f2f)
- **Config** `application.scanDirs` takes priority over a globally registered RobotLoader; previously the global loader always won and a differing value was ignored (6ef62373)

## 3.0.2.1

- **Changed** the `callback` route parameter of `NetteModule\MicroPresenter` must be a `\Closure`; other callables are rejected with `Nette\InvalidStateException` (f3b08453)
- **Changed** a request whose presenter name starts with `Nette:`, other than `Nette:Micro`, throws `Nette\Application\BadRequestException`; previously it was routed to that presenter (5d542d8f)

## 3.0.2

- **Removed** `Nette\Bridges\ApplicationLatte\Loader` (67f45fb7)
- **Deprecated** `Nette\Application\UI\Component::hasPresenter()`: use `getPresenterIfExists() !== null`; the method is silently deprecated (56e9d459)
- **Deprecated** `Nette\Application\UI\Form::hasPresenter()`: use `getPresenterIfExists() !== null`; the method is silently deprecated (56e9d459)
- **Deprecated** `Nette\Application\UI\Presenter::hasPresenter()`: use `getPresenterIfExists() !== null`; the method is silently deprecated (56e9d459)
- **Changed** the type of a persistent parameter is taken from the declared property type when there is one; previously always from the type of its default value, so typed persistent properties are converted and validated more strictly (03e94280)
- **Changed** `Nette\Application\UI\Link::__toString()` rethrows an exception raised during the conversion on PHP 7.4 and newer; previously it was turned into a fatal E_USER_ERROR (5ae6bc13)

## 3.0.1

- **Changed** `Nette\Application\Application::createInitialRequest()` throws `Nette\InvalidStateException` when the router returns parameters without a string presenter key; previously `Nette\Application\BadRequestException` (104d54e4)
- **Changed** links generated by `Nette\Application\UI\Presenter::link()` and `Nette\Application\LinkGenerator` and the baseUrl template variable are built from the URL without user info; previously a `user:pass@` part of the request was kept in them (d1378d43)

## 3.0.0

- **Removed** `Nette\Application\IRouter::SECURED`: specify the protocol directly in the route mask (afd8d321)
- **Removed** `Nette\Application\Routers\Route::$defaultFlags` (afd8d321)
- **Removed** `Nette\Application\Routers\Route::MODULE_KEY` (d05637d8)
- **Removed** `Nette\Application\Routers\Route::PRESENTER_KEY` (d05637d8)
- **Removed** `Nette\Application\Routers\SimpleRouter::MODULE_KEY` (d05637d8)
- **Removed** `Nette\Application\Routers\SimpleRouter::PRESENTER_KEY` (d05637d8)
- **Removed** `Nette\Application\Routers\CliRouter::PRESENTER_KEY` (d05637d8)
- **Removed** `Nette\Application\Routers\Route::getTargetPresenters()` (77f5dfd9)
- **Removed** `Nette\Application\Routers\RouteList::warmupCache()` (77f5dfd9)
- **Renamed** `Nette\Application\UI\Control::invalidateControl()` → `Nette\Application\UI\Control::redrawControl()`; the old name was removed (afd8d321)
- **Renamed** `Nette\Bridges\ApplicationLatte\UIMacros::renderSnippets()` → `Nette\Bridges\ApplicationLatte\UIRuntime::renderSnippets()`; the old name was removed (afd8d321)
- **Renamed** the template variable `$baseUri` → `$baseUrl`; the old name was removed (bbe70369)
- **Renamed** `|url` → `|escapeUrl`; using the old name triggers E_USER_DEPRECATED (28d429e4)
- **Replaced** `Nette\Application\UI\Control::validateControl()` with `Nette\Application\UI\Control::redrawControl($snippet, false)`: the old method was removed (afd8d321)
- **Replaced** `Nette\Application\UI\Presenter::backlink()` with `Nette\Application\UI\Presenter::getAction(true)`: the old method was removed (afd8d321)
- **Replaced** `Nette\Bridges\ApplicationLatte\Template::registerHelper()` with `$template->getLatte()->addFilter()`: the old method was removed (afd8d321)
- **Replaced** calling a filter directly on the template object, i.e. `Nette\Bridges\ApplicationLatte\Template::__call()`, with `$template->getLatte()->invokeFilter($name, $args)`: the magic method was removed (afd8d321)
- **Replaced** the `$_control` template variable with the `uiControl` provider added by `$latte->addProvider()` (afd8d321)
- **Replaced** the `$_presenter` template variable with the `uiPresenter` provider added by `$latte->addProvider()` (afd8d321)
- **Replaced** `Nette\Application\UI\Component::attached()` with an override of `Nette\Application\UI\Component::validateParent()` and a `monitor()` callback: the framework no longer calls attached() (047c42f6)
- **Replaced** `Nette\Application\UI\Form::attached()` with an override of `Nette\Application\UI\Form::validateParent()` and a `monitor()` callback; chaining via `parent::attached()` no longer works either (047c42f6)
- **Replaced** the static `Nette\Application\UI\Component::getPersistentParams()` with public properties annotated `@persistent`: overriding the method has no effect, persistent parameters are discovered by reflection only (7765c92a)
- **Replaced** `Nette\Application\Application::$maxLoop` used statically with the instance property of the same name: assigning to the class no longer has any effect (8593c507)
- **Replaced** `Nette\Application\UI\Component::getParam()` with `Nette\Application\UI\Component::getParameters()` when called without an argument and with `Nette\Application\UI\Component::getParameter()` when called with one; using it triggers E_USER_DEPRECATED (800508d6)
- **Replaced** the numeric `$code` first argument of `Nette\Application\UI\Component::redirect()` with `Nette\Application\UI\Component::redirectPermanent()` for 301; passing it triggers E_USER_DEPRECATED and other codes have no replacement (29602efe)
- **Replaced** the `module` key in `$defaults` of `Nette\Application\Routers\SimpleRouter::__construct()` with `Nette\Application\Routers\RouteList::withModule()`; passing it triggers E_USER_DEPRECATED (ac454ca8)
- **Replaced** `$metadata` of `Nette\Application\Routers\Route::__construct()` passed as `Nette\Callback` with a `\Closure` (afd8d321)
- **Deprecated** `Nette\Application\IRouter`: use `Nette\Routing\Router`; the old name is silently deprecated (77f5dfd9)
- **Deprecated** `Nette\Application\Routers\Route::$styles`: using it triggers E_USER_DEPRECATED (a0474ca5)
- **Deprecated** the `@User(loggedIn)` annotation on presenter action and render methods: check the user in the action or in `Nette\Application\UI\Presenter::checkRequirements()`; using it triggers E_USER_DEPRECATED (f90e7dca)
- **Deprecated** `|normalize`: use `Nette\Utils\Strings::normalize()`; using it triggers E_USER_DEPRECATED (28d429e4)
- **Deprecated** `|toAscii`: use `Nette\Utils\Strings::toAscii()`; using it triggers E_USER_DEPRECATED (28d429e4)
- **Deprecated** `|reverse`: using it triggers E_USER_DEPRECATED (28d429e4)
- **Deprecated** `|null`: using it triggers E_USER_DEPRECATED (28d429e4)
- **Signature** native parameter and return types were added throughout the package; affects implementations and overrides of `Nette\Application\IPresenter::run()`, `Nette\Application\IPresenterFactory::createPresenter()`, `Nette\Application\IPresenterFactory::getPresenterClass()`, `Nette\Application\IResponse::send()`, `Nette\Application\UI\IRenderable::isControlInvalid()`, `Nette\Application\UI\IRenderable::redrawControl()`, `Nette\Application\UI\ISignalReceiver::signalReceived()`, `Nette\Application\UI\IStatePersistent::loadState()`, `Nette\Application\UI\IStatePersistent::saveState()`, `Nette\Application\UI\ITemplate::render()`, `Nette\Application\UI\ITemplate::setFile()`, `Nette\Application\UI\ITemplate::getFile()`, `Nette\Application\UI\ITemplateFactory::createTemplate()`, `Nette\Bridges\ApplicationLatte\ILatteFactory::create()`, `Nette\Application\UI\Component::validateParent()`, `Nette\Application\UI\Component::checkRequirements()`, `Nette\Application\UI\Control::templatePrepareFilters()`, `Nette\Application\UI\Multiplier::createComponent()`, `Nette\Application\UI\Form::isAnchored()`, `Nette\Application\UI\Form::receiveHttpData()` and `Nette\Application\UI\Presenter::shutdown()` (b71d69c5, c94f654f, 3f03051d)
- **Signature** `Nette\Application\IRouter::match()` returns an array of parameters instead of `Nette\Application\Request`; affects custom routers, the new `Nette\Application\Request::toArray()` does the opposite conversion (ed4be551)
- **Signature** `Nette\Application\IRouter::constructUrl()` takes an array of parameters instead of `Nette\Application\Request`; affects custom routers (ed4be551)
- **Signature** `$refUrl` of `Nette\Application\IRouter::constructUrl()` is `Nette\Http\UrlScript` instead of `Nette\Http\Url`; affects custom routers (a0ddbee7)
- **Signature** `$refUrl` of `Nette\Application\LinkGenerator::__construct()` is `Nette\Http\UrlScript` instead of `Nette\Http\Url` (a0ddbee7)
- **Signature** `$reflection` of `Nette\Application\UI\Component::saveState()` was removed; affects subclasses overriding it and calls passing a second argument (86101dd9)
- **Signature** `$throw` of `Nette\Application\UI\Component::getPresenter()`, `Nette\Application\UI\Form::getPresenter()` and `Nette\Application\UI\Presenter::getPresenter()` was removed from the signature; passing it triggers E_USER_DEPRECATED, use `hasPresenter()` instead of `getPresenter(false)` (31ff0947)
- **Signature** `$message` of `Nette\Application\UI\Component::error()` and `NetteModule\MicroPresenter::error()` is `string` with the default `''`; passing null throws a `TypeError` (66c40ddc)
- **Signature** `$name` of `Nette\Application\UI\Control::getSnippetId()` is required and typed `string` (b71d69c5)
- **Signature** `$destination` of `Nette\Application\UI\Presenter::redirectPermanent()` is required and typed `string` (b71d69c5)
- **Signature** `Nette\Application\Application::getRouter()` returns `Nette\Routing\Router` instead of `Nette\Application\IRouter` (fc1a9f70)
- **Signature** these classes are final and can no longer be extended: `Nette\Application\Helpers`, `Nette\Application\LinkGenerator`, `Nette\Application\Request`, `Nette\Application\Responses\CallbackResponse`, `Nette\Application\Responses\FileResponse`, `Nette\Application\Responses\ForwardResponse`, `Nette\Application\Responses\JsonResponse`, `Nette\Application\Responses\RedirectResponse`, `Nette\Application\Responses\TextResponse`, `Nette\Application\Responses\VoidResponse`, `Nette\Application\Routers\CliRouter`, `Nette\Application\Routers\SimpleRouter`, `Nette\Application\UI\Link`, `Nette\Application\UI\Multiplier`, `Nette\Bridges\ApplicationDI\ApplicationExtension`, `Nette\Bridges\ApplicationDI\LatteExtension`, `Nette\Bridges\ApplicationDI\RoutingExtension`, `Nette\Bridges\ApplicationLatte\UIMacros`, `Nette\Bridges\ApplicationTracy\RoutingPanel`, `NetteModule\ErrorPresenter` and `NetteModule\MicroPresenter` (a70c7256)
- **Signature** these methods are final and can no longer be overridden: `Nette\Application\Application::getPresenter()`, `Nette\Application\Application::getRequests()`, `Nette\Application\UI\Component::getParameter()`, `Nette\Application\UI\Component::getParameters()`, `Nette\Application\UI\Component::getParameterId()`, `Nette\Application\UI\Control::getTemplate()`, `Nette\Application\UI\Control::setTemplateFactory()`, `Nette\Application\UI\Form::getPresenter()`, `Nette\Application\UI\Presenter::createRequest()`, `Nette\Application\UI\Presenter::getAction()`, `Nette\Application\UI\Presenter::getView()`, `Nette\Application\UI\Presenter::getLayout()`, `Nette\Application\UI\Presenter::getRequest()`, `Nette\Application\UI\Presenter::getPayload()`, `Nette\Application\UI\Presenter::getSignal()`, `Nette\Application\UI\Presenter::getUser()`, `Nette\Application\UI\Presenter::getSession()`, `Nette\Application\UI\Presenter::getContext()`, `Nette\Application\UI\Presenter::getHttpRequest()`, `Nette\Application\UI\Presenter::getHttpResponse()`, `Nette\Application\UI\Presenter::getTemplateFactory()`, `Nette\Application\UI\Presenter::isSignalReceiver()`, `Nette\Application\UI\Presenter::injectPrimary()`, `Nette\Bridges\ApplicationLatte\Template::getLatte()`, `Nette\Bridges\ApplicationLatte\Template::getFile()`, `Nette\Bridges\ApplicationLatte\Template::getParameters()` and `Nette\Bridges\ApplicationLatte\Template::__toString()` (a70c7256)
- **Changed** `Nette\Application\Routers\RouteList` extends `Nette\Routing\RouteList` instead of `Nette\Utils\ArrayList`; code relying on the ArrayList behavior breaks (77f5dfd9)
- **Changed** a signal is rejected and redirected through `Nette\Application\UI\Presenter::detectedCsrf()` when the HTTP request is not same-site; annotate the handler `@crossOrigin` to keep accepting cross-site calls (87696e09)
- **Changed** `Nette\Application\UI\Form::signalReceived()` rejects the submit signal when the HTTP request is not same-site (87696e09)
- **Changed** `Nette\Application\UI\Presenter::run()` returns `Nette\Application\Responses\VoidResponse` instead of null and always performs the shutdown, flash and AJAX payload handling; previously only when `Nette\Application\AbortException` was thrown (ad41a4cd)
- **Changed** `NetteModule\MicroPresenter::run()` returns `Nette\Application\Responses\VoidResponse` instead of null when the callback produces no response (ad41a4cd)
- **Changed** `Nette\Application\UI\Presenter::createRequest()` parses the destination with a strict regular expression; a destination that does not have the shape `[//] path[!][?query][#fragment]` throws `Nette\Application\UI\InvalidLinkException` (1c581500)
- **Changed** `NetteModule\ErrorPresenter::run()` renders the error template only when the response content type is text/html; previously always (82099365)
- **Changed** `Nette\Application\Responses\FileResponse::__construct()` falls back to the file base name only when `$name` is null; previously for any falsy value (bcaa9d91)
- **Changed** `Nette\Application\Routers\Route::constructUrl()` casts all scalar parameters to strings, so false becomes `'0'`; previously only parameters with a fixity were converted and a false default in a mask became an empty string (b62d2f52)
- **Changed** `Nette\Application\Routers\RouteList::getModule()` returns null instead of an empty string when the router was created without a module (06f98aac)
- **Changed** `Nette\Application\Routers\Route::match()` always returns the matched array and no longer throws `Nette\InvalidStateException` nor returns null when the presenter parameter is missing; the validation happens in `Nette\Application\Application::createInitialRequest()` (ae6e0bc4)
- **Changed** `Nette\Application\Routers\SimpleRouter::match()` always matches and no longer returns null when the presenter parameter is missing, so inside a `Nette\Application\Routers\RouteList` it no longer defers to the next router (ae6e0bc4)
- **Changed** `Nette\Application\UI\Presenter::changeAction()` no longer validates the action name; the validation happens once during request initialization (50e49fda)
- **Changed** `Nette\Bridges\ApplicationLatte\Template::setTranslator()` called with null keeps the `|translate` filter defined and returns the text unchanged; previously the filter was removed and using it failed (fc63a718)
- **Changed** `Nette\Application\UI\Control::getTemplate()` fails with a native `TypeError` when `createTemplate()` returns something else than a template; previously `Nette\UnexpectedValueException` with a descriptive message (bf347899)
- **Changed** `Nette\Application\PresenterFactory::getPresenterClass()` fails with a native `TypeError` for a non-string name; previously `Nette\Application\InvalidPresenterException` (bf347899)
- **Changed** `NetteModule\MicroPresenter` throws `Nette\InvalidStateException` (500) when the route has no callback parameter; previously `Nette\Application\BadRequestException` (404) (b7df270f)
- **Changed** presenter services registered by `Nette\Bridges\ApplicationDI\ApplicationExtension` are not autowired; reference a presenter explicitly when another service needs it (2a8d32a4)
- **Config** the `nette.presenter` DI service tag is no longer used, presenters are found by type (cb354357)
