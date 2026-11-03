# Package Name

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Wire the package through `spatie/laravel-package-tools` in the service provider's `configurePackage()` and follow its conventions: config, views, translations, and publish tags use the package short name (`package-name`), translations live in `resources/lang`, assets in `resources/dist`.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `vendor-name/laravel-package-name`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit package code over helper abstractions unless the extension point is real.
- Write the least code that does the job: no private helpers, cache layers, or defensive checks the behavior does not need.
- Prefer Laravel collections and typed accessors (`collect()`, `$response->collect()`, `->integer()`, `->string()`) over hand-written array handling and casts.
- For large or repeated array shapes, define a `@phpstan-type` on the class that owns the data and reuse it with `@phpstan-import-type` instead of repeating the shape.
- This package is public. Never write secrets, credentials, personal data, or internal details into code, comments, tests, commit messages, or pull requests.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.
- Never lower the quality bar to get green: no PHPStan baseline or `ignoreErrors`, no `@codeCoverageIgnore`, no lowered coverage thresholds.

## External Services

- Put every network service the package calls behind an interface in `src/Contracts`, named after the role it plays, not the vendor: `PaymentGateway`, not `Stripe`.
- Name the implementation after vendor and role (`StripePaymentGateway`) and bind it to the interface in the service provider, so an application can swap it.
- The interface speaks the package's language and describes returned shapes with `@phpstan-type`. Vendor URLs, payloads, and credentials stay inside the implementation.
- Only the implementation reads its credentials, from the package config (`config('package-name.…')`), where it uses them.
- Only the implementation's own tests fake HTTP with `Http::fake()`. Every other test binds a fake of the interface.

## Data

- When two requests can pass the same check before a write (a limit, the last remaining record), lock the row with `lockForUpdate()` in a short `DB::transaction()` and check again under the lock.
- Name every date or time column after the moment it records, ending in `_at`: `published_at`, not `publish_date`.
- Never log secrets, tokens, request bodies, or personal data. Log identifiers and status codes.
- Store sensitive content with Laravel's encrypted casts.

## Quick Commands

- Full validation: `composer test` (composer.json validation, audit, lint, type coverage, static analysis, tests)
- Before reporting a change as done, run `composer test` and report each step's actual result. Name steps that failed or could not run instead of reporting success, and report pre-existing failures and environment blockers separately from failures the change introduced.
- Apply Rector and Pint fixes: `composer lint`
- Lint check: `composer test:lint`
- Static analysis (PHPStan max): `composer test:types`
- Type coverage (100%): `composer test:type-coverage`
- Pest with exact 100% line coverage: `composer test:unit`
- composer.json validation: `composer test:composer`
- Dependency audit: `composer test:audit`
- Workbench build: `composer build`
- Workbench server: `composer serve`

## GitHub Workflows

- Workflows only read the code and report a result. Declare `permissions: contents: read` at the top level and never raise it; a feature that needs write access does not run in GitHub Actions.
- Trigger only on `push`, `pull_request`, `schedule`, or `workflow_dispatch`, never on `pull_request_target`, `workflow_run`, or comment and issue events.
- Never interpolate `${{ github.event.* }}`, `${{ github.head_ref }}`, or other user-controlled values into `run:`. Pass them through `env:`.
- Use no secrets. Nothing in this repository deploys or publishes from a workflow.
- Pin third-party actions to a full commit SHA with the version in a comment, and only use actions from GitHub or from the vendor of the installed tool.
- Check out with `persist-credentials: false`, run on GitHub-hosted runners, and give every job a `timeout-minutes`.
- Review every change under `.github/` for its permissions, triggers, secrets, and interpolations before anything else.

## Pull Requests

- Use `.github/pull_request_template.md` as the body. Fill every section, remove the prompts, and lead with the problem and the resulting behavior.
- Keep one task per pull request. Reuse an existing pull request for the same scope instead of opening a duplicate.
- Work on a branch from an up-to-date `main`. Keep unrelated changes and temporary files out of the diff.
- Reproduce a bug with a failing test before fixing it.
- Record the exact results of `composer test` under Validation, and distinguish local checks from CI.
- Review your own diff before publishing and remove debug code and temporary changes.
- Push and open or update a pull request only when the user asked for it. Never merge or enable auto-merge without explicit approval.
- If creating the pull request fails, check for an existing one before retrying. Report the URL, the final commit, what ran, and what is left open.

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4/5 and Orchestra Testbench.
- `package-release`: use when preparing tags, GitHub releases and their release notes, or release automation changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.
