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

it('removes the configure test commands from the Composer scripts', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0)
        ->and(File::json("{$this->package}/composer.json")['scripts'])->not->toHaveKey('test:configure')
        ->toHaveKey('test', ['@test:composer', '@test:lint', '@test:type-coverage', '@test:types', '@test:unit']);
});

it('removes the configure tests even when a later step fails', function (): void {
    $result = configurePackage($this->package, ['--no-interaction'], env: ['COMPOSER_LINT_FAILS' => '1']);

    expect($result->exitCode())->toBe(1)
        ->and("{$this->package}/configure.php")->toBeFile()
        ->and("{$this->package}/tests/Configure")->not->toBeDirectory();
});
