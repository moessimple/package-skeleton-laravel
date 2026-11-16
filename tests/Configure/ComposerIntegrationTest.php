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

// The only test that runs the real Composer, against its own physical copy of vendor/ and
// composer.lock. `composer remove` resolves the lock file against Packagist, so it needs the network.
it('removes the configure dependencies from Composer and autoloads the new namespace with the real Composer', function (): void {
    copyDependencies($this->package);
    $skeletonAutoload = hash_file('sha256', skeletonPath().'/vendor/composer/autoload_psr4.php');

    $result = configurePackage($this->package, [
        '--no-interaction',
        '--package-name=acme/laravel-shop-tools',
        '--author-name=Jane Doe',
        '--author-email=jane@example.com',
    ], env: [
        'PATH' => "{$this->sandbox}/bin".PATH_SEPARATOR.getenv('PATH'),
        'COMPOSER_BINARY' => false,
    ], withDependencies: false, timeout: 600);

    $validation = Process::path($this->package)->run(['composer', 'validate', '--no-check-publish']);

    expect($result->exitCode())->toBe(0, $result->output().$result->errorOutput())
        ->and(configureResult($result->output()))->toHaveKey('success', true)
        ->and(File::json("{$this->package}/composer.json")['require-dev'])->not->toHaveKeys(['laravel/agent-detector', 'laravel/chisel', 'laravel/prompts'])
        ->and(File::json("{$this->package}/composer.lock")['packages-dev'])->each->not->toHaveKey('name', 'laravel/chisel')
        ->and($validation->exitCode())->toBe(0, $validation->output().$validation->errorOutput())
        ->and(File::get("{$this->package}/vendor/composer/autoload_psr4.php"))->toContain("'Acme\\\\ShopTools\\\\' => array(\$baseDir . '/src')")
        ->and(hash_file('sha256', skeletonPath().'/vendor/composer/autoload_psr4.php'))->toBe($skeletonAutoload);
})->group('composer');
