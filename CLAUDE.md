# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Nette Application is a full-stack component-based MVC framework library for PHP. It provides the core application layer for the Nette Framework, handling presenters, components, routing, templating integration, and request/response cycles.

**Key characteristics:**
- PHP library package (not a full application)
- Supports PHP 8.2 - 8.5
- Component-based architecture with hierarchical component trees
- Tight integration with Latte templating engine
- Dependency injection through Nette DI bridges

## Essential Commands

### Running Tests

```bash
# Run all tests
vendor/bin/tester tests -s -C

# Run specific test directory
vendor/bin/tester tests/Application -s -C
vendor/bin/tester tests/UI -s -C

# Run single test file
php tests/Application/Presenter.twoDomains.phpt
```

**Important:** Tests use Nette Tester with `.phpt` extension (148 test files total).

### Code Quality

```bash
# Run PHPStan static analysis (level 5)
composer phpstan
```

## Architecture

### Source Structure

```
src/
├── Application/              Main application layer
│   ├── UI/                  Presenter & component system
│   │   ├── Presenter.php   Base presenter class
│   │   ├── Component.php   Base component class
│   │   ├── Control.php     Renderable component
│   │   └── Form.php        Form component integration
│   ├── Routers/            Routing implementations
│   │   ├── Route.php       Standard route
│   │   ├── RouteList.php   Route collection
│   │   ├── SimpleRouter.php
│   │   └── CliRouter.php   CLI routing
│   ├── Responses/          Response types
│   │   ├── JsonResponse.php
│   │   ├── TextResponse.php
│   │   ├── RedirectResponse.php
│   │   ├── ForwardResponse.php
│   │   └── FileResponse.php
│   ├── Application.php     Front controller
│   ├── Request.php         Application request
│   ├── LinkGenerator.php   URL generation
│   └── PresenterFactory.php
└── Bridges/                Framework integrations
    ├── ApplicationDI/      DI container integration
    │   ├── ApplicationExtension.php
    │   ├── LatteExtension.php
    │   └── RoutingExtension.php
    ├── ApplicationLatte/   Latte template engine integration
    │   ├── TemplateFactory.php
    │   ├── TemplateGenerator.php  Auto-generates template classes
    │   ├── UIExtension.php
    │   └── Nodes/         Latte syntax nodes
    └── ApplicationTracy/   Tracy debugger integration
```

### Key Architectural Concepts

#### Component Hierarchy

The framework uses hierarchical component trees where:
- **Presenter** is the root component (extends `Control`, implements `IPresenter`)
- **Control** components can contain child components
- **Component** base class provides parameter persistence and signal handling
- Components use `lookup()` to find ancestors (e.g., `$this->lookup(Presenter::class)`)

#### Request-Response Cycle

1. `Application` receives HTTP request via router
2. `PresenterFactory` creates presenter instance
3. `Presenter::run()` processes the request:
   - Calls lifecycle methods (`startup()`, `beforeRender()`, `render*()`, `shutdown()`)
   - Handles signals (AJAX/component interactions)
   - Resolves action/view
   - Creates template and renders response
4. Returns `Response` object (Text/Json/Redirect/Forward/File/Void)

#### Template Integration

**TemplateGenerator** (new in v3.3):
- Automatically generates typed template classes from presenters/controls
- Creates `{PresenterName}Template` classes with proper property types
- Updates presenter phpDoc with `@property-read` annotations
- Enables full IDE support in `.latte` files via `{templateType}` declaration

**Bridge Pattern:**
- `Bridges\ApplicationLatte` provides Latte integration
- `Bridges\ApplicationDI` provides DI container extensions
- `Bridges\ApplicationTracy` provides debugging integration

#### Link Generation

- `link()` and `n:href` generate URLs to presenter actions/signals
- `LinkGenerator` handles absolute URL generation
- Invalid link handling modes: Silent/Warning/Exception/Textual
- Special syntax: `this`, `//absolute`, `:Module:Presenter:action`

### Test Structure

Tests mirror source structure:
```
tests/
├── Application/        Application class tests
├── Bridges.DI/         DI extension tests
├── Bridges.Latte/      Latte integration tests (snippets, templates)
├── Bridges.Latte3/     Latte 3.x specific tests
├── Routers/            Router tests
├── Responses/          Response type tests
├── UI/                 Presenter & component tests
└── bootstrap.php       Test environment setup
```

**Test conventions:**
- Use `.phpt` extension (Nette Tester format)
- Use `test()` function for test cases with descriptive names
- Use `testException()` for exception-only tests
- Use `Assert::*` for assertions
- Mockery for mocking dependencies
- Temporary files go to `tests/tmp/{pid}/`

## Development Guidelines

### Coding Standards

- Every PHP file must start with `declare(strict_types=1);`
- Follow Nette Coding Standard (based on PSR-12)
- Use tabs for indentation
- Return type declarations on separate line from closing brace:
  ```php
  public function example(string $param):
  {
      // method body
  }
  ```

### Documentation Style

- Use phpDoc for public APIs
- Document `@property-read` for magic properties
- Document array types: `@return string[]`
- Mark deprecated features with `@deprecated` and explanation
- Interfaces use marker comments for method purposes

### Presenter Lifecycle

Presenters follow a strict lifecycle when processing requests. Methods are called sequentially, all optional:

```
__construct()
    ↓
startup()           ← Always call parent::startup()
    ↓
action<Action>()    ← Called BEFORE render, can change view/redirect
    ↓
handle<Signal>()    ← Processes signals (AJAX requests, component interactions)
    ↓
beforeRender()      ← Common template setup
    ↓
render<View>()      ← Prepare data for template
    ↓
afterRender()       ← Rarely used
    ↓
shutdown()          ← Cleanup
```

**Important notes:**
- `action<Action>()` executes before `render<View>()` - can change which template renders
- Parameters from URL are automatically passed and type-checked
- Missing/invalid parameters trigger 404 error
- Calling `redirect()`, `error()`, `sendJson()` etc. terminates lifecycle immediately (throws `AbortException`)
- If no response method called, presenter automatically renders template

### Persistent Parameters

Persistent parameters maintain state across requests automatically via URL.

**Declaration:**
```php
use Nette\Application\Attributes\Persistent;

class ProductPresenter extends Nette\Application\UI\Presenter
{
    #[Persistent]
    public string $lang = 'en';  // Must be public, specify type
}
```

**How they work:**
- Value automatically included in all generated links
- Transferred across different actions of same presenter
- Can be transferred across presenters if defined in common ancestor/trait
- Changed via `n:href="Product:show lang: cs"` or reset via `lang: null`

**Validation:**
Override `loadState()` to validate values from URL:
```php
public function loadState(array $params): void
{
    parent::loadState($params);  // Sets $this->lang
    if (!in_array($this->lang, ['en', 'cs'])) {
        $this->error();  // 404 if invalid
    }
}
```

**Never trust URL parameters** - always validate as users can modify them.

### Signals & Hollywood Style

Signals handle user interactions on the current page (sorting, AJAX updates, form submissions).

**Hollywood Style Philosophy:**
Instead of asking "was button clicked?", tell framework "when button clicked, call this method". Framework calls you back - "Don't call us, we'll call you."

**Signal Declaration:**
```php
public function handleClick(int $x, int $y): void
{
    // Process the signal
    $this->redrawControl();  // Mark for AJAX re-render
}
```

**Signal URLs:**
- Created with exclamation mark: `n:href="click! $x, $y"`
- Always called on current presenter/action
- Cannot signal to different presenter
- Format: `?do={signal}` or `?do={component}-{signal}` for component signals

**In components**, `link()` and `n:href` default to signals (no `!` needed):
```php
// In component template
<a n:href="refresh">refresh</a>  // Calls handleRefresh() signal
```

### Component Factory Pattern

Components are created lazily via factory methods in presenters.

**Basic Factory:**
```php
protected function createComponentPoll(): PollControl
{
    return new PollControl;
}
```

**Accessing components:**
```php
$poll = $this->getComponent('poll');  // or $this['poll']
```

**Factory is called automatically:**
- First time component is accessed
- Only if actually needed
- Not called during AJAX if component not used

**Components with Dependencies:**
Use generated factory interface pattern:
```php
// Define interface
interface PollControlFactory
{
    public function create(int $pollId): PollControl;
}

// Register in config/services.neon
// Nette DI automatically implements interface

// Use in presenter
class PollPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PollControlFactory $pollControlFactory,
    ) {
    }

    protected function createComponentPollControl(): PollControl
    {
        return $this->pollControlFactory->create($pollId: 1);
    }
}
```

**Multiplier for dynamic components:**
```php
protected function createComponentShopForm(): Multiplier
{
    return new Multiplier(function (string $itemId) {
        $form = new Nette\Application\UI\Form;
        // ... configure form for $itemId
        return $form;
    });
}

// In template: {control "shopForm-$item->id"}
```

### Bidirectional Routing

Router converts URLs ↔ Presenter:action pairs in both directions.

**Key concept:** URLs are never hardcoded. Change entire URL structure by modifying router only.

**Route definition:**
```php
$router = new RouteList;
$router->addRoute('rss.xml', 'Feed:rss');
$router->addRoute('article/<id \d+>', 'Article:view');
$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
```

**Route order is critical:**
- Evaluated top to bottom for both matching and generating
- List specific routes before general routes
- First matching route wins

**Route features:**
- Parameters: `<year>`, `<id \d+>` (with regex validation)
- Optional sequences: `[<lang>/]name`
- Default values: `<year=2020>`
- Filters & translations: Czech URLs like `/produkt` → `Product` presenter
- Wildcards: `%domain%`, `%basePath%`
- Modules: `->withModule('Admin')`
- Subdomains: `->withDomain('admin.example.com')`

**Canonization:**
Framework prevents duplicate content by redirecting alternative URLs to canonical URL (first matching route). Automatic 301 redirect for SEO.

### AJAX & Snippets

Snippets update page parts without full reload.

**Workflow:**
1. Mark element as snippet in template: `{snippet header}...{/snippet}`
2. Invalidate snippet in signal handler: `$this->redrawControl('header')`
3. Nette renders only changed snippets, sends as JSON
4. Naja.js library updates DOM automatically

**Template syntax:**
```latte
{snippet header}
    <h1>Hello {$user->name}</h1>
{/snippet}

{* Or using n:snippet attribute *}
<article n:snippet="header" class="foo">
    <h1>Hello {$user->name}</h1>
</article>
```

**Signal handler:**
```php
public function handleLogin(string $user): void
{
    $this->user = $user;
    $this->redrawControl('header');  // Invalidate specific snippet
    // or $this->redrawControl() to invalidate all snippets
}
```

**Dynamic snippets with snippetArea:**
```latte
<ul n:snippetArea="itemsContainer">
    {foreach $items as $id => $item}
        <li n:snippet="item-{$id}">{$item}</li>
    {/foreach}
</ul>
```

```php
$this->redrawControl('itemsContainer');  // Must invalidate parent area
$this->redrawControl('item-1');          // And specific snippet
```

**Client-side (Naja.js):**
```html
<!-- Add ajax class to make AJAX request -->
<a n:href="click!" class="ajax">Click me</a>
<form n:name="form" class="ajax">...</form>
```

**Sending custom data:**
```php
public function handleDelete(int $id): void
{
    // ...
    if ($this->isAjax()) {
        $this->payload->message = 'Deleted successfully';
    }
}
```

### Template Lookup

Framework automatically finds templates - no need to specify paths.

**Action template lookup:**
```
Presentation/Home/HomePresenter.php
Presentation/Home/default.latte      ← Found automatically
```

**Alternative structure:**
```
Presenters/HomePresenter.php
Presenters/templates/Home.default.latte   ← or
Presenters/templates/Home/default.latte   ← or
```

**Layout template lookup:**
```
Presentation/@layout.latte              ← Common for all
Presentation/Home/@layout.latte         ← Specific for Home
```

**Override in code:**
```php
$this->setView('otherView');                    // Change view
$this->template->setFile('/path/to/file.latte'); // Explicit path
$this->setLayout('layoutAdmin');                 // Different layout
$this->setLayout(false);                         // No layout
```

**Type-safe templates:**
```php
/**
 * @property-read ArticleTemplate $template
 */
class ArticlePresenter extends Nette\Application\UI\Presenter
{
}

class ArticleTemplate extends Nette\Bridges\ApplicationLatte\Template
{
    public Article $article;
    public User $user;
}
```

In template:
```latte
{templateType App\Presentation\Article\ArticleTemplate}
{* Now full IDE autocomplete for $article, $user *}
```

### Link Syntax

Links to presenters/actions use special syntax instead of URLs.

**In templates:**
```latte
<a n:href="Product:show $id">detail</a>
<a n:href="Product:show $id, lang: en">detail in EN</a>
<a n:href="this">refresh current page</a>
<a n:href="this page: 2">current page with changed param</a>

{* Absolute URL *}
<a n:href="//Product:show $id">absolute</a>

{* Module navigation *}
<a n:href=":Admin:Product:show $id">absolute to module</a>

{* Signals (notice the !) *}
<a n:href="click! $x, $y">signal</a>

{* Fragment *}
<a n:href="Home:#main">jump to #main</a>
```

**In presenter code:**
```php
$url = $this->link('Product:show', $id);
$url = $this->link('Product:show', [$id, 'lang' => 'en']);

// Redirects
$this->redirect('Product:show', $id);              // 302/303
$this->redirectPermanent('Product:show', $id);     // 301
$this->redirectUrl('https://example.com');
$this->forward('Product:show');                    // No HTTP redirect
```

**Link checking:**
```latte
{if isLinkCurrent('Product:show')}active{/if}
<li n:class="isLinkCurrent('Product:*') ? active">...</li>
```

**Invalid links:**
Configured via `Presenter::$invalidLinkMode`:
- `InvalidLinkSilent` - returns `#`
- `InvalidLinkWarning` - logs E_USER_WARNING (production default)
- `InvalidLinkTextual` - shows error in link text (dev default)
- `InvalidLinkException` - throws exception

### Flash Messages

Flash messages survive redirects and stay for 30 seconds (for page refresh tolerance).

```php
public function handleDelete(int $id): void
{
    // ... delete item
    $this->flashMessage('Item deleted successfully.');
    $this->redirect('this');
}
```

In template:
```latte
{foreach $flashes as $flash}
    <div class="flash {$flash->type}">{$flash->message}</div>
{/foreach}
```

With type:
```php
$this->flashMessage('Error occurred', 'error');
$this->flashMessage('Success', 'success');
```

### Application Configuration

Key configuration options in `config/common.neon`:

```neon
application:
    errorPresenter: Error        # 4xx and 5xx errors
    # Or separate error presenters:
    errorPresenter:
        4xx: Error4xx
        5xx: Error5xx

    silentLinks: false           # Suppress invalid link warnings in dev

    mapping:                     # Presenter name → class mapping
        *: App\*Module\Presentation\*Presenter

    aliases:                     # Short aliases for links
        home: Front:Home:default
        admin: Admin:Dashboard:default

latte:
    strictTypes: false           # Add declare(strict_types=1) to templates
    strictParsing: false         # Strict parser mode
    locale: cs_CZ               # Locale for filters
```

### Breaking Changes

This is v3.3 branch. Recent BC breaks include:
- `Application::processRequest()` now returns `Response` (not void)
- `@annotations` deprecated in favor of PHP 8 attributes
- Various deprecation notices for old APIs
