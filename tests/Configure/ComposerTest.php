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

it('reports a failed formatting step with its output and stops before removing dependencies', function (): void {
    $result = configurePackage($this->package, ['--no-interaction'], env: ['COMPOSER_LINT_FAILS' => '1']);

    expect($result->exitCode())->toBe(1)
        ->and(configureResult($result->output()))
        ->toHaveKey('success', false)
        ->toHaveKey('errors', ['Code formatting failed: Pint found style issues.'])
        ->and(stubCalls($this->sandbox))->toContain('composer lint')
        ->not->toContain('composer remove --dev --no-install --no-scripts --no-audit --quiet laravel/agent-detector laravel/chisel laravel/prompts')
        ->not->toContain('composer dump-autoload --quiet')
        ->and("{$this->package}/configure.php")->toBeFile();
});

it('reports a failed dependency removal with its output and keeps configure.php', function (): void {
    $result = configurePackage($this->package, ['--no-interaction'], env: ['COMPOSER_REMOVE_FAILS' => '1']);

    expect($result->exitCode())->toBe(1)
        ->and(configureResult($result->output()))
        ->toHaveKey('success', false)
        ->toHaveKey('errors', ['Could not remove configuration dependencies: Could not resolve the dependencies.'])
        ->and(stubCalls($this->sandbox))->not->toContain('composer dump-autoload --quiet')
        ->and("{$this->package}/configure.php")->toBeFile();
});

it('reports a dependency removal that leaves the packages in composer.json', function (): void {
    $result = configurePackage($this->package, ['--no-interaction'], env: ['COMPOSER_REMOVE_KEEPS_PACKAGES' => '1']);

    expect($result->exitCode())->toBe(1)
        ->and(configureResult($result->output()))
        ->toHaveKey('success', false)
        ->toHaveKey('errors', ['Could not remove configuration dependencies: '])
        ->and(stubCalls($this->sandbox))->not->toContain('composer dump-autoload --quiet')
        ->and("{$this->package}/configure.php")->toBeFile();
});

it('reports a failed autoloader rebuild after configure.php is already removed', function (): void {
    $result = configurePackage($this->package, ['--no-interaction'], env: ['COMPOSER_DUMP_FAILS' => '1']);

    expect($result->exitCode())->toBe(1)
        ->and(configureResult($result->output()))
        ->toHaveKey('success', false)
        ->toHaveKey('errors', ['Autoload generation failed: Could not write the autoloader.'])
        ->and("{$this->package}/configure.php")->not->toBeFile();
});

it('runs Composer from the PATH when COMPOSER_BINARY is not set', function (): void {
    installStub($this->sandbox, 'composer');

    $result = configurePackage($this->package, ['--no-interaction'], env: ['COMPOSER_BINARY' => false]);

    expect($result->exitCode())->toBe(0)
        ->and(stubCalls($this->sandbox))->toContain(
            'composer lint',
            'composer remove --dev --no-install --no-scripts --no-audit --quiet laravel/agent-detector laravel/chisel laravel/prompts',
            'composer dump-autoload --quiet',
        );
});
