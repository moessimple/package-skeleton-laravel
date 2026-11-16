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

Package Description

## Installation

You can install the package via Composer:

```bash
composer require vendor-name/laravel-package-name
```

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="package-name-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="package-name-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="package-name-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="package-name-translations"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="package-name-assets"
```

## Usage

<!-- Add a basic usage example here. -->

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
- `composer test`: the full gate, in order: `composer.json` validation, lint, type coverage, static analysis, tests.

## Changelog

Please see the [releases](https://github.com/vendor-name/laravel-package-name/releases) for what has changed in each version.

## Contributing

Thank you for considering contributing to Package Name! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Author Name](https://github.com/vendor-name)
- [All Contributors](../../contributors)

## License

Package Name is open-sourced software licensed under the [MIT license](LICENSE.md).
