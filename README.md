<!-- package-header:start
<div align="center">
    <h1>Package Name</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/vendor-name/laravel-package-name"><img src="https://img.shields.io/packagist/v/vendor-name/laravel-package-name.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/vendor-name/laravel-package-name"><img src="https://img.shields.io/packagist/php-v/vendor-name/laravel-package-name.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/vendor-name/laravel-package-name"><img src="https://badge.laravel.cloud/badge/vendor-name/laravel-package-name?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/vendor-name/laravel-package-name/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/vendor-name/laravel-package-name/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/vendor-name/laravel-package-name"><img src="https://img.shields.io/packagist/dt/vendor-name/laravel-package-name.svg?style=flat-square" alt="Total Downloads"></a>
</p>
package-header:end -->

<!-- template:start -->
<div align="center">
    <h1>Package Skeleton Laravel</h1>
</div>

<p align="center">
    <a href="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/tests.yml"><img src="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/tests.yml/badge.svg?branch=main" alt="Tests"></a>
    <a href="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/static.yml"><img src="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/static.yml/badge.svg?branch=main" alt="Static analysis"></a>
    <a href="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/lint.yml"><img src="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/lint.yml/badge.svg?branch=main" alt="Lint"></a>
    <a href="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/security.yml"><img src="https://github.com/moessimple/package-skeleton-laravel/actions/workflows/security.yml/badge.svg?branch=main" alt="Security"></a>
</p>

## Introduction

A template for Laravel 12 and 13 packages on PHP 8.4 or newer. It comes with:

- a service provider built on [spatie/laravel-package-tools](https://github.com/spatie/laravel-package-tools), with placeholder config, views, translations, assets, migration, route file and Artisan command
- one quality gate, `composer test`: `composer validate`, dependency audit, Rector and Pint, 100% type coverage, PHPStan at level max, and Pest with exactly 100% line coverage
- GitHub Actions for lint, static analysis, a weekly security audit, and tests across PHP 8.4 and 8.5, Laravel 12 and 13, lowest and newest dependencies, on Linux and Windows
- an Orchestra Testbench workbench (`composer serve`) and a Laravel Boost skill for apps that install the package

## Getting Started

> **Requires PHP 8.4+, Composer 2.10+, and a code coverage driver like
> [Xdebug](https://xdebug.org/docs/install) or [PCOV](https://github.com/krakjoe/pcov).**

1. Create a repository with **Use this template** and clone it.
2. Run the configure script.

    ```bash
    php configure.php
    ```

    The script asks for the package and author details and shows what it will change. After you confirm, it turns the template into your package, installs the dependencies and runs the full quality gate.

    To run it without questions, for example from a coding agent, pass `--no-interaction` and the answers as options. `php configure.php --help` lists them.

    ```bash
    php configure.php --no-interaction --vendor=acme --package=laravel-example --author-name="Jane Doe" --author-email=jane@example.com
    ```

3. GitHub does not copy repository settings from a template. Add a ruleset for `main` that requires a pull request before merging and the status checks `lint`, `static`, `security` and `tests`, so every change goes through a pull request and a failing check blocks the merge. Set the number of required approvals to fit your team.

Everything below is the README of your package. The configure script removes everything above this line and activates the package header.

<!-- template:end -->

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via Composer:

```bash
composer require vendor-name/laravel-package-name
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="package-name-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="package-name-config"
```

This is the contents of the published config file:

```php
return [

    'placeholder' => 'default',

];
```

Optionally, you can publish the views, translations and public assets using:

```bash
php artisan vendor:publish --tag="package-name-views"
php artisan vendor:publish --tag="package-name-translations"
php artisan vendor:publish --tag="package-name-assets"
```

## Usage

```php
use VendorName\PackageName\Facades\PackageName;

// Add a basic usage example here.
```

## Available Tooling

### Development

- `composer serve`: builds the workbench and serves a Laravel app with the package installed.
- `composer build`: builds the workbench without serving it: publishes the assets and migrates a fresh SQLite database.

### Security

- `composer test:audit`: checks the installed dependencies for known vulnerabilities and abandoned packages.

### Code Quality

- `composer lint`: Rector and Pint, fixing in place.
- `composer test:lint`: the same checks read-only (Rector `--dry-run`, Pint `--test`).
- `composer test:composer`: validates `composer.json` strictly.

### Testing

- `composer test:types`: PHPStan at `level: max` over the package, tests and workbench.
- `composer test:type-coverage`: fails under 100% type coverage (Pest).
- `composer test:unit`: Pest in parallel under an exact 100% line-coverage gate.
- `composer test`: the full gate, in order: `composer.json` validation, audit, lint, type coverage, static analysis, tests.

## Changelog

Please see the [releases](https://github.com/vendor-name/laravel-package-name/releases) for what has changed in each version.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Author Name](https://github.com/vendor-name)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
