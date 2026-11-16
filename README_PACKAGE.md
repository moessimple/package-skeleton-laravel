<div align="center">
    <h1>:package_name</h1>
</div>

<p align="center">
    <a href="https://github.com/:vendor_slug/:package_slug/actions/workflows/tests.yml"><img src="https://github.com/:vendor_slug/:package_slug/actions/workflows/tests.yml/badge.svg?branch=main" alt="Tests"></a>
    <a href="https://github.com/:vendor_slug/:package_slug/actions/workflows/static.yml"><img src="https://github.com/:vendor_slug/:package_slug/actions/workflows/static.yml/badge.svg?branch=main" alt="Static analysis"></a>
    <a href="https://github.com/:vendor_slug/:package_slug/actions/workflows/lint.yml"><img src="https://github.com/:vendor_slug/:package_slug/actions/workflows/lint.yml/badge.svg?branch=main" alt="Lint"></a>
</p>

:package_description

## Installation

You can install the package via Composer:

```bash
composer require :vendor_slug/:package_slug
```

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag=":package_short_name-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag=":package_short_name-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag=":package_short_name-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag=":package_short_name-translations"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag=":package_short_name-assets"
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

Please see the [releases](https://github.com/:vendor_slug/:package_slug/releases) for what has changed in each version.

## Contributing

Thank you for considering contributing to :package_name! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [:author_name](https://github.com/:author_username)
- [All Contributors](../../contributors)

## License

:package_name is open-sourced software licensed under the [MIT license](LICENSE.md).
