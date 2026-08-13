# {linkBase} tag - compile-time base for relative links in a template

Status: accepted · 2025-02-27 · branch `v3.2` (released in 3.2.7)

## Context

A flexible directory structure is one of the most valuable features of Nette
Application, but it creates a problem with shared layouts: relative link targets
(`Dashboard:`, `Products:Overview:`) are resolved against the *current*
presenter. When the same layout is used by both `Admin:Dashboard` and
`Admin:Products:Detail`, the target `Dashboard:` resolves correctly to
`Admin:Dashboard:default` in one case and to the non-existent
`Admin:Products:Dashboard:default` in the other.

The previous workaround was to write every target in layouts absolutely
(`:Admin:Dashboard:`). It works, but the module prefix is duplicated in every
link, so moving or renaming a module means editing templates en masse.

## Considered options

- **Status quo (absolute links everywhere)** - no new concept, but prefix
  duplication and fragility when refactoring modules.
- **Runtime solution** (setting the base on the presenter/component, or a
  template variable threaded into `LinkGenerator`) - collides with the shared
  template cache: the same compiled layout is used by presenters from different
  modules, so the base would have to become part of the cache key, or a runtime
  cost would be paid on every link. It would also make the template no longer
  self-describing - a link's target would depend on state set somewhere in PHP.
- **Compile-time tag in the template** (chosen) - the base is declared directly
  in the template it concerns, and expansion happens at compile time.

## Decision

A new tag `{linkBase module}` plus a compile-time pass `applyLinkBasePass`
registered in `UIExtension::getPasses()`:

- The tag must appear in the template head (`Tag::isInHead()`, otherwise
  `CompileException`) and applies to the **whole template file** - not to
  included files, which are compiled separately and may carry their own
  `{linkBase}`. It emits no code itself (`print()` returns `''`); all the work
  is done by the pass. Only the first tag found is used
  (`NodeHelpers::findFirst`).
- The pass traverses the AST and rewrites **all** `LinkNode`s - i.e. `{link}`,
  `{plink}` and `n:href` uniformly.
- When both the target and the base are static strings, the destination is
  rewritten directly at compile time (`LinkGenerator::applyBase()` called from
  the pass) - **zero runtime overhead**. When either is an expression, the
  destination is wrapped in a runtime call `LinkGenerator::applyBase(%node,
  %node)` via `AuxiliaryNode`.
- Semantics of `LinkGenerator::applyBase($link, $base)`: the prefix `:$base:` is
  added only when the target **contains a colon and does not start with one**.
  Thus the base applies exclusively to relative targets that name a presenter
  (`Users:list` → `:Admin:Users:list`); untouched are absolute targets
  (`:Foo:`), targets within the current presenter (`show`, `this`), signals
  (`click!`) and aliases - none of which have a colon in the middle.

## Consequences

- (+) Layouts and templates shared by presenters at different module depths link
  consistently, regardless of who renders them.
- (+) Renaming or moving a module is a single change in `{linkBase}` instead of
  editing every link.
- (+) For static links, expansion happens at compile time; nothing is paid at
  runtime.
- (−) Another tag and concept to learn; the per-template-file scope may surprise
  (includes do not inherit the base).
- (−) The base cannot be set per presenter/module from PHP - that remains open.

## Code

- `src/Bridges/ApplicationLatte/Nodes/LinkBaseNode.php` - the tag and the
  `applyLinkBasePass` pass
- `src/Application/LinkGenerator.php` - `applyBase()` (`@internal`)
- tag and pass registration in `src/Bridges/ApplicationLatte/UIExtension.php`
  (`getTags()`, `getPasses()`)
- test: `tests/Bridges.Latte/{linkBase}.phpt` (static and dynamic targets)
- commit `e3beab847`, released in `v3.2.7`

The `|absoluteUrl` filter shipped in the same release for forcing an absolute URL
on an arbitrary value (image path, database value), complementing this tag for
cases outside presenter link targets.
