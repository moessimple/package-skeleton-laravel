# Package Skeleton Laravel

<p align="center">
    <a href="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/tests.yml"><img src="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/tests.yml/badge.svg?branch=main" alt="Tests"></a>
    <a href="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/static.yml"><img src="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/static.yml/badge.svg?branch=main" alt="Static analysis"></a>
    <a href="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/lint.yml"><img src="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/lint.yml/badge.svg?branch=main" alt="Lint"></a>
</p>

A starter template for building beautiful Laravel packages.

## Introduction

This skeleton provides everything you need to start building a Laravel package. It comes pre-configured with a service provider built on spatie/laravel-package-tools, testing via Pest, static analysis via Larastan, Rector and Pint, and a workbench application for end-to-end development, all wired up and ready to go. `composer test` checks Composer metadata, lint, PHPStan at level max, and 100% line and type coverage.

An interactive configuration script personalizes the skeleton for your package during `composer install`, setting up your namespace, service provider, and only the features you need.

## Getting Started

Press the **Use this template** button at the top of this repository to create your package, or clone it directly:

```bash
git clone https://github.com/moessimple/package-skeleton-laravel.git my-package
cd my-package
```

Requires PHP 8.4+, Composer 2.11+, and Xdebug or PCOV for line coverage.

Then, install your dependencies. The interactive configuration script will run automatically:

```bash
composer install
```

If you prefer to configure manually, install without scripts and run the configuration separately:

```bash
composer install --no-scripts
php configure.php
```

Once configured, verify everything is working:

```bash
composer test
```

You may also boot the included workbench application to test your package end-to-end:

```bash
composer serve
```

The workbench app will be available at `http://localhost:8000`.

## Non-Interactive Configuration

The configuration script supports non-interactive mode for CI or scripted setups. Pass `--no-interaction` along with any metadata options you'd like to prefill:

```bash
php configure.php --no-interaction --config --routes
```

Non-interactive mode also activates automatically when the `COMPOSER_NO_INTERACTION=1` environment variable is set, when an AI agent is detected, or when standard input is not an interactive terminal.

Omitting feature flags includes every package feature; passing specific flags includes only those features. Tools work the same way: omitting tool flags such as `--dependabot` or `--security-policy` includes every tool, while passing specific flags includes only those tools.

Pass `--package-name=acme/laravel-example`, `--vendor-namespace=Acme`, `--class-name=Example`, `--package-description="An example package."`, `--author-name="Jane Doe"` and `--author-email=jane@example.com` to prefill the metadata.

Non-interactive runs print a single line of JSON describing the result, including the resolved metadata, selected features and tools, and any manual follow-up steps. Invalid metadata options fail with a JSON error before any files are changed.

During configuration, `README_PACKAGE.md` and `AGENTS_PACKAGE.md` are customized and moved to `README.md` and `AGENTS.md`, replacing the skeleton files. The script also links `CLAUDE.md` to `AGENTS.md` and `.claude` to `.agents` so both agent formats share the same guidance.

## After Setup

A few GitHub settings need your attention after creating your package repository:

- Require pull requests and the `lint`, `static` and `tests` status checks in a ruleset for `main`. Choose required approvals to fit your team.
- Enable Dependabot alerts and security updates.
- Review Dependabot pull requests before merging. This skeleton does not include an automatic merge workflow.

GitHub releases are the changelog. Workflows only read the repository and report results; they do not push commits. No additional repository secrets are required.

Configuration removes `configure.php`, its Composer hooks, its configure-only dependencies, its tests and the skeleton's configure workflow. The template documents become the package documents, and the generated package keeps its tests and development tools.

## Developing the Skeleton

Changes to the skeleton itself start from `composer install --no-scripts`, so the skeleton stays unconfigured. `composer test` then also runs `composer test:configure`, the tests in `tests/Configure` that run `configure.php` against temporary copies of the skeleton. One of them runs the real Composer and needs network access.
