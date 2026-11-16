<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->sandbox = createSandbox();
    $this->package = copySkeleton("{$this->sandbox}/laravel-example");
    $this->original = directoryContents($this->package);
});

afterEach(function (): void {
    File::deleteDirectory($this->sandbox);
});

it('prints the usage with every option and changes nothing', function (string $flag): void {
    $result = configurePackage($this->package, [$flag]);

    expect($result->exitCode())->toBe(0)
        ->and($result->output())->toContain('Usage: php configure.php [options]')
        ->toContain('-n, --no-interaction')
        ->toContain('--package-name')
        ->toContain('--boost-skill')
        ->toContain('--security-policy')
        ->toContain('--installer-dir')
        ->and(directoryContents($this->package))->toBe($this->original);
})->with(['--help', '-h']);

it('rejects an invalid option as a JSON error and changes nothing', function (string $option, string $error): void {
    $result = configurePackage($this->package, [$option]);

    expect($result->exitCode())->toBe(1)
        ->and($result->output())->json()->toMatchArray([
            'success' => false,
            'errors' => [$error],
        ])
        ->and(directoryContents($this->package))->toBe($this->original);
})->with([
    'an unknown option' => ['--unknown-option', 'The "--unknown-option" option does not exist.'],
    'a metadata option without a value' => ['--author-name', 'The "--author-name" option requires a value.'],
]);

it('reports missing dependencies as a JSON error and changes nothing', function (): void {
    $result = configurePackage($this->package, ['--no-interaction'], withDependencies: false);

    expect($result->exitCode())->toBe(1)
        ->and($result->output())->json()->toMatchArray([
            'success' => false,
            'errors' => ['Composer dependencies are not installed. Run `composer install` before `php configure.php`.'],
        ])
        ->and(directoryContents($this->package))->toBe($this->original);
});
