# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

`cpsit/typo3-handlebars` is a TYPO3 CMS extension (PHP) that provides a complete Handlebars template rendering environment. It integrates with TYPO3's Symfony DI container, content object system, and PSR-14 event dispatcher.

## Commands

All development commands run via Composer scripts. The `.Build/` directory is the project build root (vendor at `.Build/vendor/`, binaries at `.Build/bin/`).

```bash
# Install dependencies
composer install

# Run all checks (deps, refactor, static analysis, style)
composer check

# Style checks (dry-run)
composer check:style           # all style checks
composer check:style:php       # PHP-CS-Fixer
composer check:style:typoscript # TypoScript lint
composer check:style:composer  # composer normalize
composer check:style:editorconfig # EditorConfig

# Static analysis (PHPStan level max)
composer check:static

# Rector dry-run
composer check:refactor:php

# Dependency analysis
composer check:deps

# Auto-fix
composer fix               # all fixers (composer, editorconfig, PHP)
composer fix:php           # PHP-CS-Fixer

# Apply Rector refactoring
composer refactor:php

# Tests
composer test              # unit + functional
composer test:unit         # unit only
composer test:functional   # functional only (requires MySQL)

# Run a single test file or filter
composer test:unit -- Tests/Unit/Renderer/HandlebarsRendererTest.php
composer test:unit -- --filter testSomeMethodName

# Docs
composer docs              # build and open docs
```

## Architecture

### Core Rendering Pipeline

The central entry point is `HandlebarsRenderer` (`Classes/Renderer/HandlebarsRenderer.php`). It:
1. Receives a `RenderingContext` (template path + variables + request)
2. Resolves the template file via a `TemplateResolver`
3. Compiles the Handlebars template (with optional `HandlebarsCache`)
4. Dispatches PSR-14 events (`BeforeTemplateCompilationEvent`, `BeforeRenderingEvent`, `AfterRenderingEvent`)
5. Executes the compiled template with resolved variables

### Template Resolution

`TemplateResolver` implementations (`Classes/Renderer/Template/`) locate `.hbs` template files. `TemplatePaths` holds the ordered list of search paths, populated by `PathProvider` implementations (TypoScript-configured, global, or content-object-based).

The default resolver is `FlatTemplateResolver` (aliased via `#[AsAlias(TemplateResolver::class)]`). Names prefixed with `@` trigger flat resolution: all root paths are scanned recursively and the file is looked up by bare filename regardless of directory. `@name--variant` falls back to `@name` if no exact match exists — this follows Fractal's naming convention. Names without `@` are passed to `HandlebarsTemplateResolver`, which resolves them as directory-relative paths against the root paths.

### Helper System

Custom Handlebars helpers implement `Helper` and are auto-registered via the `#[AsHelper]` PHP attribute (`Classes/Attribute/AsHelper.php`). The Symfony DI compiler pass `HandlebarsHelperPass` collects all tagged services and registers them in `HelperRegistry` at container compile time. Built-in helpers live in `Classes/Renderer/Helper/`.

`#[AsHelper]` method resolution (in `Configuration/Services.php`): explicit `method` param → `ReflectionMethod` name if placed on a method → `render` if class implements `Helper` → `__invoke`.

`HelperRegistry::mapFunctionParameters` (called at render time, not compile time) injects arguments by type-hint: `HelperOptions` and `RenderingContext` are resolved from the type, in any order, before positional (untyped) arguments. A non-nullable `RenderingContext` parameter throws if the context is absent.

### Variable System

Variables flow through `VariableProvider` implementations into `VariableBag`. The `VariablesProcessor` merges providers (TypoScript-sourced, global, etc.) before rendering. `MarkerBasedValueProcessor` handles `###MARKER###`-style substitution.

### TYPO3 Integration Points

- **Content Object**: `HandlebarsTemplateContentObject` (`Classes/Frontend/`) — TYPO3 `tt_content` rendering via `lib.contentElement`
- **Data Processors**: `ProcessVariablesProcessor`, `ResolveMarkersProcessor`, `UnflattenVariableNamesProcessor` — used in TypoScript to prepare variables
- **View**: `HandlebarsView` / `HandlebarsViewFactory` (`Classes/View/`) — implements `ViewInterface` for controller-based rendering, with fallback to Fluid. `HandlebarsViewFactory` reads the `handlebars` key from the plugin's Extbase framework configuration (`plugin.tx_<ext>_<plugin>.handlebars`). Four resolution keys are merged from least to most specific: `default`, `<ControllerAlias>`, `<ControllerAlias>::<actionName>`, `<ControllerFQCN>`. Each accepts `HANDLEBARSTEMPLATE` properties (`templateName`, `format`, `templateRootPaths`, etc.). If no `handlebars` key is present but the controller extends `HandlebarsController`, the template name defaults to `<ControllerAlias>/<actionName>`.
- **Cache**: `HandlebarsCache` wraps TYPO3's caching framework; `NullCache` for development

### Extension Points

Four interfaces are designed to be replaced or extended by consuming extensions:

| Interface | Registration | Notes |
|---|---|---|
| `Renderer\Renderer` | `alias:` in `Services.yaml` | Replace full rendering stack |
| `Renderer\Template\TemplateResolver` | `alias:` in `Services.yaml` | Replace template/partial path resolution; `BaseTemplateResolver` provides helpers |
| `Renderer\Template\Path\PathProvider` | Auto via `#[AutoconfigureTag('handlebars.template_path_provider')]` | Contribute paths; requires `getPriority()` |
| `Renderer\Variables\VariableProvider` | Auto via `#[AutoconfigureTag('handlebars.variable_provider')]` | Inject global variables; extends `\ArrayAccess`; requires `getPriority()` |

`DataProcessing\DataSource\DataSourceAwareProcessor` is used for `preProcessing`/`postProcessing` hooks inside `ProcessVariablesProcessor` and `HandlebarsTemplateContentObject`. Implementations are referenced by FQCN in TypoScript and instantiated via `GeneralUtility::makeInstance()` (see `SupportsDataSourceAwareProcessing` trait). `DataSourceCollection::resolve()` searches the four data sources (`ProcessorConfiguration`, `ProcessedData`, `ContentObjectRenderer`, `ContentObjectConfiguration`) in priority order.

### DI Configuration

Services are wired in `Configuration/Services.yaml` and `Configuration/Services.php`. The `HandlebarsExtension` Symfony DI extension (`Classes/DependencyInjection/`) handles custom configuration. Helper auto-registration happens in `HandlebarsHelperPass`.

## Code Conventions

- All files have `declare(strict_types=1)` and a GPL-2.0 license header (enforced by PHP-CS-Fixer via `Build/checks/.php-cs-fixer.php`)
- PHPStan runs at **level max** — all new code must pass without adding to the baseline (`Build/checks/phpstan-baseline.neon`)
- Classes are `final` by default; use `readonly` properties where possible, but prefer `final readonly` classes
- Namespace root: `CPSIT\Typo3Handlebars\`, tests: `CPSIT\Typo3Handlebars\Tests\`

## Testing

Unit tests (`Tests/Unit/`) test classes in isolation. Functional tests (`Tests/Functional/`) spin up a real TYPO3 instance and require a MySQL database (configured via environment variables for CI). Functional test fixtures and a companion TYPO3 extension live in `Tests/Functional/Fixtures/`.

Coverage reports are written to `Build/tests/coverage/` (HTML under `html/_merged/`, merged Clover XML at `clover.xml`). Run `composer test:coverage` to generate them.
