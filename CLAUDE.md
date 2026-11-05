# Package Name

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Wire the package through `spatie/laravel-package-tools` in the service provider's `configurePackage()` and follow its conventions: config, views, translations, and publish tags use the package short name (`package-name`), translations live in `resources/lang`, assets in `resources/dist`.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `vendor-name/laravel-package-name`.
- Add only the code, files, and dependencies the behavior needs: no speculative helpers, cache layers, or defensive checks.
- Prefer explicit package code over helper abstractions unless the extension point is real.
- Prefer Laravel collections and typed accessors over hand-written array handling and casts: `collect()`, `$response->collect()` and `$response->fluent()->integer('id')` for HTTP responses, `$request->string('name')`, `config()->string('package-name.key')`.
- For large or repeated array shapes, define a `@phpstan-type` on the class that owns the data and reuse it with `@phpstan-import-type` instead of repeating the shape.
- Treat the repository as public. Never write secrets, credentials, personal data, or internal details into code, comments, tests, commit messages, or pull requests.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.
- Never lower the quality bar to get green: no PHPStan baseline or `ignoreErrors`, no `@codeCoverageIgnore`, no lowered coverage thresholds.

## External Services

- Put a network service the package's behavior depends on behind an interface in `src/Contracts`, named after the role it plays, not the vendor: `PaymentGateway`, not `Stripe`. Tests bind a fake of the interface and applications can swap the vendor, so this extension point is real.
- Name the implementation after vendor and role (`StripePaymentGateway`). Bind it in the service provider with a closure that reads its config with typed accessors (`config()->string('package-name.api_key')`) and passes the values to the constructor. Mark credential parameters with `#[\SensitiveParameter]`.
- The interface speaks the package's language. It returns a small readonly value object or an array described with `@phpstan-type`. Vendor URLs and payloads stay inside the implementation.
- Only the implementation's own tests fake HTTP with `Http::fake()`. Every other test binds a fake of the interface.

## Data

- Guarantee uniqueness with a unique index, and handle a violation with `createOrFirst()` or by catching `UniqueConstraintViolationException`.
- When a write depends on a check over other rows (a limit, the last remaining admin), lock the parent row with `lockForUpdate()` in a short `DB::transaction()` and check again under the lock, or use a conditional update and check the affected row count. Every writer must lock the same parent row. The SQLite test database ignores `lockForUpdate()`, so tests cannot prove the lock.
- Name timestamp columns after the moment they record, ending in `_at`: `published_at`, not `publish_time`.
- Hash passwords with the `hashed` cast. Store tokens the package looks up as `hash('sha256', $token)` in an indexed column, as Sanctum does, because a salted hash cannot be found with a query. Encrypt values it must read back, such as third-party credentials, with the `encrypted` cast in a `text` column. Encrypted columns cannot be filtered, indexed, or unique.
- Never log secrets, tokens, request bodies, or personal data. Log identifiers and status codes. A `RequestException` message contains the start of the response body, so where a vendor response can carry sensitive data, catch it and throw a package exception without the body. Do not pass the `RequestException` as `previous`: the log includes previous exceptions.

## Quick Commands

- Full validation: `composer test` (composer.json validation, lint, type coverage, static analysis, tests)
- Apply Rector and Pint fixes: `composer lint`
- Lint check: `composer test:lint`
- Static analysis (PHPStan max): `composer test:types`
- Type coverage (100%): `composer test:type-coverage`
- Pest with exact 100% line coverage: `composer test:unit`
- composer.json validation: `composer test:composer`
- Dependency audit: `composer test:audit`
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
- `package-release`: use when preparing tags, GitHub releases and their release notes, or release automation changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.
