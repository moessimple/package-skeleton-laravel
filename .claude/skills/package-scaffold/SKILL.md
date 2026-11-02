---
name: package-scaffold
description: "Use this skill when adding Laravel package capabilities or wiring them through the service provider: commands, migrations, routes, config merges, views, translations, assets, middleware, publish tags, workbench files, or console-only behavior."
license: MIT
metadata:
  author: laravel
---

# Package Scaffold

## Primary Goal

Add package features in the right place and wire them through the service provider with `spatie/laravel-package-tools`, keeping package names, namespaces, config keys, and publish tags consistent.

## Workflow

1. Inspect the existing package structure, sibling examples, README setup notes, and the current service provider before creating files.
2. Identify whether the request touches commands, migrations, routes, config, views, translations, assets, middleware, tests, README/contributing docs, compatibility, or release flow.
3. Create the capability files under the paths laravel-package-tools expects and use the configured package names, namespaces, publish tags, URLs, and badges consistently.
4. Wire the capability through the service provider using the patterns in *Provider wiring* below.
5. Use `package-testing` for coverage, update README or contributing documentation when user-facing behavior changes, use `package-compatibility` for matrix-sensitive changes, and use `package-release` for release tasks.
6. Add only the files needed for the requested capability and validate with the narrowest relevant command before broader checks.

## Provider Wiring

1. Declare package resources in `configurePackage()` with the `Package` methods: `hasConfigFile()`, `hasViews()`, `hasTranslations()`, `hasAssets()`, `hasRoute()`, `hasMigration()`, `hasConsoleCommand()`, `hasInstallCommand()`.
2. Put container bindings in `packageRegistered()` and boot-time logic the `Package` methods do not cover in `packageBooted()`.
3. Register Artisan commands with `hasConsoleCommand()`, which only registers them in the console. `hasCommand()` also registers them for `Artisan::call()` from web requests; use it only when that is intended.
4. Let laravel-package-tools name the publish tags (`package-name-config`, `-views`, `-translations`, `-assets`, `-migrations`) from the package short name, which is the name after `laravel-`.
5. Add tests for the observable provider behavior: merged config, loaded routes, publish tags, or command registration.

Provider wiring anti-patterns:

- Loading host app state too early during provider registration.
- Calling `env()` outside config files; read config values instead.
- Registering web-only concerns unconditionally when the package can run in console contexts.
- Bypassing `configurePackage()` with hand-written `publishes()` or `load*From()` calls for a resource laravel-package-tools supports.

## References

- `src/*ServiceProvider.php`
- `config/*.php`
- `routes/*.php`
- `resources/views/`
- `resources/lang/`
- `resources/dist/`
- `database/migrations/`
- `src/Console/Commands/`
- `tests/Feature/` and `tests/Unit/`
- [laravel-package-tools documentation](https://github.com/spatie/laravel-package-tools)

## Examples

- Add an Artisan command: create the command class under `src/Console/Commands`, register it with `hasConsoleCommand()`, add a feature test for observable console output, and document the command if it is user-facing.
- Add a publishable migration: place the migration without a timestamp in `database/migrations`, register it with `hasMigration()`, which publishes it under the `package-name-migrations` tag with a fresh timestamp, and test publish behavior with Testbench.

## Anti-Patterns

- Adding unused files because a package might need them later.
- Mixing package names, namespaces, config keys, or publish tags.
- Changing dependencies without approval.
- Replacing explicit Laravel package code with a helper abstraction when one feature-specific change would do.
