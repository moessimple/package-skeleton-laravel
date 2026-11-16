<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->sandbox = createSandbox();
    $this->package = copySkeleton("{$this->sandbox}/laravel-example");
});

afterEach(function (): void {
    File::deleteDirectory($this->sandbox);
});

it('leaves no skeleton placeholder in the generated package', function (): void {
    $result = configurePackage($this->package, [
        '--no-interaction',
        '--package-name=acme/laravel-shop-tools',
        '--author-name=Jane Doe',
        '--author-email=jane@example.com',
    ]);

    $placeholders = '/:(author_name|author_email|author_username|vendor_name|vendor_slug|vendor_namespace|package_name|package_slug|package_short_name|package_description|class_name)|vendor-name|VendorName|PackageName|package-name|package_name|Package Name|Author Name|author@example\.com|moessimple/';

    expect($result->exitCode())->toBe(0)
        ->and(array_keys(array_filter(directoryContents($this->package), fn (string $contents): bool => preg_match($placeholders, $contents) === 1)))->toBe([]);
});

it('renames the files named after the skeleton package', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--package-name=acme/laravel-shop-tools']);

    expect($result->exitCode())->toBe(0)
        ->and(array_keys(directoryContents($this->package)))->each->not->toContain('PackageName')->not->toContain('package-name')->not->toContain('package_name')
        ->and(configureResult($result->output())['summary']['removed_paths'])->toContain('src/PackageNameServiceProvider.php', 'config/package-name.php');
});

it('does not replace placeholder text inside the inserted values', function (): void {
    $result = configurePackage($this->package, [
        '--no-interaction',
        '--package-name=acme/laravel-shop-tools',
        '--package-description=Wraps the :package_slug API of vendor-name for PackageName users.',
    ]);

    expect($result->exitCode())->toBe(0)
        ->and(File::get("{$this->package}/README.md"))->toContain('Wraps the :package_slug API of vendor-name for PackageName users.')
        ->and(File::json("{$this->package}/composer.json"))->toHaveKey('description', 'Wraps the :package_slug API of vendor-name for PackageName users.');
});

it('leaves binary files, Composer files and skipped directories untouched', function (): void {
    $binary = "\x89PNG\r\n\x1a\n\0PackageName vendor-name";
    File::put("{$this->package}/resources/dist/logo.png", $binary);
    File::put("{$this->package}/composer.lock", '{"packages": [{"name": "vendor-name/laravel-package-name"}]}');
    File::ensureDirectoryExists("{$this->package}/vendor/acme");
    File::put("{$this->package}/vendor/acme/PackageName.php", 'PackageName vendor-name');
    File::ensureDirectoryExists("{$this->package}/node_modules/acme");
    File::put("{$this->package}/node_modules/acme/index.js", 'PackageName vendor-name');
    File::ensureDirectoryExists("{$this->package}/resources/js");
    File::ensureDirectoryExists("{$this->package}/build/cache");
    File::put("{$this->package}/build/cache/PackageName.txt", 'PackageName vendor-name');
    $composer = File::json("{$this->package}/composer.json");
    $composer['support'] = ['issues' => 'https://github.com/vendor-name/laravel-package-name/issues'];
    File::put("{$this->package}/composer.json", json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

    $result = configurePackage($this->package, ['--no-interaction', '--package-name=acme/laravel-shop-tools']);

    expect($result->exitCode())->toBe(0)
        ->and(File::get("{$this->package}/resources/dist/logo.png"))->toBe($binary)
        ->and(File::get("{$this->package}/composer.lock"))->toBe('{"packages": [{"name": "vendor-name/laravel-package-name"}]}')
        ->and(File::get("{$this->package}/vendor/acme/PackageName.php"))->toBe('PackageName vendor-name')
        ->and(File::get("{$this->package}/node_modules/acme/index.js"))->toBe('PackageName vendor-name')
        ->and(File::get("{$this->package}/build/cache/PackageName.txt"))->toBe('PackageName vendor-name')
        ->and("{$this->package}/resources/js")->toBeDirectory()
        ->and(File::json("{$this->package}/composer.json"))->toHaveKey('support.issues', 'https://github.com/vendor-name/laravel-package-name/issues');
});

it('leaves configure.php untouched when a step fails', function (): void {
    $original = File::get("{$this->package}/configure.php");

    $result = configurePackage($this->package, ['--no-interaction', '--package-name=acme/laravel-shop-tools'], env: ['COMPOSER_LINT_FAILS' => '1']);

    expect($result->exitCode())->toBe(1)
        ->and(File::get("{$this->package}/configure.php"))->toBe($original);
});

it('removes every Chisel marker', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--views', '--facade']);

    expect($result->exitCode())->toBe(0)
        ->and(array_keys(array_filter(directoryContents($this->package), fn (string $contents): bool => str_contains($contents, '@chisel') || str_contains($contents, '@end-chisel'))))->toBe([]);
});

it('removes the directories that are left empty', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/resources")->not->toBeDirectory()
        ->and("{$this->package}/database")->not->toBeDirectory()
        ->and("{$this->package}/src/Console")->not->toBeDirectory()
        ->and("{$this->package}/.github/workflows")->toBeDirectory()
        ->and(configureResult($result->output())['summary']['removed_paths'])->toContain('resources', 'database', 'src/Console');
});

it('completes when optional files are already missing', function (): void {
    File::deleteDirectory("{$this->package}/config");
    File::delete([
        "{$this->package}/.github/FUNDING.yml",
        "{$this->package}/.github/CONTRIBUTING.md",
        "{$this->package}/tests/Feature/OutsideConsoleTest.php",
    ]);

    $result = configurePackage($this->package, ['--no-interaction', '--views', '--security-policy']);

    expect($result->exitCode())->toBe(0)
        ->and(configureResult($result->output()))->toHaveKey('success', true)
        ->and("{$this->package}/configure.php")->not->toBeFile();
});

it('completes without the package templates and the example test', function (): void {
    File::delete([
        "{$this->package}/README.md",
        "{$this->package}/README_PACKAGE.md",
        "{$this->package}/AGENTS.md",
        "{$this->package}/AGENTS_PACKAGE.md",
        "{$this->package}/tests/Feature/ExampleTest.php",
    ]);

    $result = configurePackage($this->package, ['--no-interaction', '--routes']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/README.md")->not->toBeFile()
        ->and("{$this->package}/AGENTS.md")->not->toBeFile()
        ->and("{$this->package}/CLAUDE.md")->not->toBeFile()
        ->and("{$this->package}/configure.php")->not->toBeFile();
});
