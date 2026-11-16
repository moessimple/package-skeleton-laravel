<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

beforeEach(function (): void {
    $this->sandbox = createSandbox();
    $this->package = copySkeleton("{$this->sandbox}/laravel-example");
});

afterEach(function (): void {
    File::deleteDirectory($this->sandbox);
});

it('removes configure.php and the skeleton-only files from the generated package', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/configure.php")->not->toBeFile()
        ->and("{$this->package}/tests/Configure")->not->toBeDirectory()
        ->and("{$this->package}/.github/workflows/configure.yml")->not->toBeFile()
        ->and("{$this->package}/.agents/skills/skeleton-development")->not->toBeDirectory();
});

it('removes the configure test registrations and keeps the package test suites', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0)
        ->and(File::get("{$this->package}/tests/Pest.php"))->not->toContain('Configure')
        ->toContain("->in('Feature', 'Unit')")
        ->and(File::get("{$this->package}/phpunit.xml.dist"))->not->toContain('Configure')
        ->not->toContain('defaultTestSuite')
        ->toContain('<testsuite name="Feature">')
        ->toContain('<testsuite name="Unit">');
});

it('removes the configure hooks and test commands from the Composer scripts', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);
    $composer = File::json("{$this->package}/composer.json");

    expect($result->exitCode())->toBe(0)
        ->and($composer)->not->toHaveKeys(['scripts.post-install-cmd', 'scripts.post-update-cmd', 'scripts.test:configure'])
        ->toHaveKeys(['scripts.post-autoload-dump', 'scripts.clear', 'scripts.lint'])
        ->and(data_get($composer, 'scripts.test'))->not->toContain('@test:configure')
        ->toContain('@test:lint', '@test:types', '@test:unit');
});

it('removes configure.php and the configure tests from the PHPStan and Rector configuration', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0)
        ->and(File::get("{$this->package}/phpstan.neon.dist"))->not->toContain('configure.php')
        ->toContain('- src/')
        ->and(File::get("{$this->package}/rector.php"))->not->toContain('configure.php')
        ->not->toContain('tests/Configure')
        ->toContain("__DIR__.'/src'")
        ->and(Process::run([PHP_BINARY, '-l', "{$this->package}/rector.php"])->exitCode())->toBe(0);
});

it('removes the configure tests even when a later step fails', function (): void {
    $result = configurePackage($this->package, ['--no-interaction'], env: ['COMPOSER_LINT_FAILS' => '1']);

    expect($result->exitCode())->toBe(1)
        ->and("{$this->package}/tests/Configure")->not->toBeDirectory();
});

it('completes a second run after a failed step', function (): void {
    configurePackage($this->package, ['--no-interaction', '--package-name=acme/laravel-shop-tools'], env: ['COMPOSER_LINT_FAILS' => '1']);

    $result = configurePackage($this->package, ['--no-interaction', '--package-name=acme/laravel-shop-tools']);

    expect($result->exitCode())->toBe(0)
        ->and(configureResult($result->output()))->toHaveKey('success', true)
        ->and("{$this->package}/configure.php")->not->toBeFile()
        ->and("{$this->package}/src/ShopToolsServiceProvider.php")->toBeFile()
        ->and(File::get("{$this->package}/CLAUDE.md"))->toBe(File::get("{$this->package}/AGENTS.md"))
        ->and(File::json("{$this->package}/composer.json"))->toHaveKey('name', 'acme/laravel-shop-tools');
});
