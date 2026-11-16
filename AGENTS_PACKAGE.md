# Package Name

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Wire package resources through `spatie/laravel-package-tools` in `configurePackage()`. Config, view and translation namespaces and publish tags use the package short name. Translations live in `resources/lang` and assets in `resources/dist`.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `vendor-name/laravel-package-name`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.
- Never lower the quality bar: no PHPStan baseline, `ignoreErrors`, `@codeCoverageIgnore`, or lowered coverage thresholds.
- Treat the repository as public. Never write secrets, credentials, personal data or internal details into code, tests, documentation or GitHub text.

## Quick Commands

- Full validation: `composer test`
- Apply Rector and Pint fixes: `composer lint`
- Lint check: `composer test:lint`
- Static analysis (PHPStan max): `composer test:types`
- Type coverage (100%): `composer test:type-coverage`
- Pest tests (100% line coverage): `composer test:unit`
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Before Reporting Done

- Run `composer test` and report each step's actual result. Name steps that failed or could not run instead of reporting success.
- Report pre-existing failures and environment blockers separately from failures the change introduced.

## GitHub Workflows

- Workflows only read the code and report a result. Declare `permissions: contents: read` at the top level and never raise it; a feature that needs write access does not run in GitHub Actions.
- Trigger only on `push`, `pull_request`, `schedule`, or `workflow_dispatch`, never on `pull_request_target`, `workflow_run`, or comment and issue events.
- Never interpolate `${{ github.event.* }}`, `${{ github.head_ref }}`, or other user-controlled values into `run:`. Pass them through `env:`.
- Use no secrets.
- Pin every action outside this repository to a full commit SHA with the version in a comment. Use actions from GitHub (`actions/*`) and `shivammathur/setup-php`; add another one only with its reason in the pull request.
- Check out with `persist-credentials: false`, run on GitHub-hosted runners, and give every job a `timeout-minutes`.
- Review changes under `.github/` against these rules first.

## Reviews

- Review a change once, against its goal, for correctness and missing tests, not style.
- Re-review only after fixing Critical or Required findings, and only to check those fixes.
- Stop when no Critical or Required finding is open. Optional findings and nits do not start another round; fix them or list them under Limitations.
- If a Critical or Required finding is still open after the second round, stop and hand it to the user.
- Count a finding only when code, documentation, or a test backs it.

## Pull Requests

- Give the pull request a title that describes the change for users: the release notes are generated from pull request titles.
- Use `.github/pull_request_template.md` as the body, remove its prompts, and lead with the problem and the resulting behavior.
- Keep one task per pull request. Reuse an existing pull request for the same scope instead of opening a duplicate.
- Work on a branch from an up-to-date `main`. Keep unrelated changes and temporary files out of the diff.
- Put the `composer test` results under Validation, local checks apart from CI.
- Review your own diff before publishing and remove debug code and temporary changes.
- Push and open or update a pull request only when the user asked for it. Never merge or enable auto-merge without explicit approval.
- If creating the pull request fails, check for an existing one before retrying. Report the URL, the final commit, what ran, and what is left open.

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4/5 and Orchestra Testbench.
- `package-release`: use when preparing tags, GitHub releases and their generated release notes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.
