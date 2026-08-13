# Template & layout lookup

Templates are found by convention; the candidate paths and their order are the only
non-obvious part.

## Action template candidates (`formatTemplateFiles`)

Starting from the presenter's own directory:

- **If there is no `templates/` subdirectory**, the lookup climbs one level; if
  there is still none, the single candidate is `"<dir>/<view>.latte"` (the
  "template next to the presenter" layout).
- **If a `templates/` subdirectory exists**, two candidates are tried, in order:
  1. `"<dir>/templates/<Presenter>/<view>.latte"`
  2. `"<dir>/templates/<Presenter>.<view>.latte"`

`findTemplateFile()` returns the first that `is_file`, else calls `error()` → **404
"Missing template"**. Remember (see lifecycle.md) this resolution happens **lazily
at `sendTemplate`**, via `completeTemplate`, only when the template has no explicit
file.

## Layout candidates (`formatLayoutTemplateFiles`)

- A `$layout` containing a slash (or backslash) is taken as a direct path.
- Otherwise the base name is `<layout>` (default `layout`). With a `templates/`
  directory the candidates are `templates/<Presenter>/@<layout>.latte` and
  `templates/<Presenter>.@<layout>.latte` (once, at the presenter's level only),
  then the shared `templates/@<layout>.latte` repeated **up the module levels**
  (`substr_count(name, ':')` levels of `dirname`). Without a `templates/`
  directory, `@<layout>.latte` next to the presenter, then walking up the same way.
- `setLayout(false)` disables the layout entirely (returns `null`); an explicitly
  named layout that is not found throws `FileNotFoundException`, while the default
  layout being absent is simply `null` (optional).

## Typed templates

`formatTemplateClass` derives the template class from the presenter/control name:
for a presenter it tries `<Base><Action>Template` then `<Base>Template` (stripping
the `Presenter` suffix); for a control, `<Base>Template` (stripping `Control`). The
class is validated (`is_a(..., Template::class)`) or a `trigger_error` degrades to
the default. `TemplateFactory::createTemplate` instantiates it around a fresh Latte
engine and injects the default variables (`user`, `baseUrl`, `basePath`, `flashes`,
`control`, `presenter`).

## `#[TemplateVariable]`

At `sendTemplate`, `completeTemplate()` copies every `#[TemplateVariable]` property
of the presenter into the template via `$template->$name ??= $this->$name` — an
explicitly assigned template variable wins. The property must be **public**
(`LogicException` otherwise); uninitialized properties are skipped (unless they
have a get hook, PHP 8.4+). Presenter-only — plain controls don't run
`completeTemplate`.

The `{templateType}` Latte tag is **not** in this package — it is implemented in
Latte core; here the typing is only the name-derived class plus the
`@property-read <X>Template $template` docblock.
