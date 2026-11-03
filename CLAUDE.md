# Package Name

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Wire the package through `spatie/laravel-package-tools` in the service provider's `configurePackage()` and follow its conventions: config, views, translations, and publish tags use the package short name (`package-name`), translations live in `resources/lang`, assets in `resources/dist`.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `vendor-name/laravel-package-name`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.
- Never lower the quality bar to get green: no PHPStan baseline or `ignoreErrors`, no `@codeCoverageIgnore`, no lowered coverage thresholds.

## Quick Commands

- Full validation: `composer test` (composer.json validation, audit, lint, type coverage, static analysis, tests)
- Before reporting a change as done, run `composer test` and report each step's actual result. Name steps that failed or could not run instead of reporting success.
- Apply Rector and Pint fixes: `composer lint`
- Lint check: `composer test:lint`
- Static analysis (PHPStan max): `composer test:types`
- Type coverage (100%): `composer test:type-coverage`
- Pest with exact 100% line coverage: `composer test:unit`
- composer.json validation: `composer test:composer`
- Dependency audit: `composer test:audit`
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4/5 and Orchestra Testbench.
- `package-release`: use when preparing tags, GitHub releases and their release notes, or release automation changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.
