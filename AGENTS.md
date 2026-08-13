# To My Agents!

It is my fervent wish that this file guide every AI coding agent working with code in this repository.

## Documentation

Any distilled, agent-facing documentation for this package - how it works
internally and the rationale behind key design decisions - lives in `docs/`.
Consult it before non-trivial changes; it is the source of truth from which the
public manual is distilled.

The presenter lifecycle, link generation, persistent state, routing, and template
lookup carry subtle non-local invariants - read `docs/internals` before touching
them.

## Project Overview

Nette Application is a full-stack, component-based MVC framework library for PHP -
the core application layer of the Nette Framework: presenters, components, routing,
templating integration, and the request/response cycle. It is a library package,
not a runnable application, and integrates with the rest of the framework through
`src/Bridges` (DI, Latte, Tracy).

- **PHP Version**: 8.3 - 8.5
- **Package**: `nette/application`

## Essential Commands

```bash
# Run all tests
composer tester            # or: vendor/bin/tester tests -s

# Run one test directory / file
vendor/bin/tester tests/UI -s
php tests/Application/Presenter.twoDomains.phpt

# Static analysis (PHPStan level 8)
composer phpstan
```

## Conventions

- Every file starts with `declare(strict_types=1);`; tabs for indentation; Nette
  Coding Standard (PSR-12 based) with the opening brace on its own line.
- Tests are Nette Tester `.phpt` files mirroring `src/` under `tests/`; use
  `test()` / `testException()` and `Assert::*`. Temp files go to `tests/tmp/{pid}/`,
  and `tests/types/` holds PHPStan type-inference fixtures.

## Working in this repo

- **The presenter lifecycle is exact and load-bearing.** The precise phase order
  (action, canonicalization, signal dispatch, `beforeRender`, `render`, the
  `onRender` hook, `afterRender`), `AbortException`-based termination, and the
  4xx-not-500 invalid-link contract are documented in `docs/internals/lifecycle.md`.
  Don't reconstruct them from memory.
- **Links and persistent state span the whole component tree.** Link-generation
  modes and whole-tree global-state aggregation are the trickiest code here; see
  `docs/internals/link-generation.md` and `persistent-state.md`.
- User-facing how-to (routing DSL, signals, snippets, component factories, flash
  messages, DI/NEON configuration) is manual material and lives in the public web
  docs, not here.
