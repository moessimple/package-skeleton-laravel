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

it('rejects an invalid value with its reason and changes nothing', function (string $option, string $error): void {
    $result = configurePackage($this->package, ['--no-interaction', $option]);

    expect($result->exitCode())->toBe(1)
        ->and(configureResult($result->output()))
        ->toHaveKey('success', false)
        ->toHaveKey('errors', [$error])
        ->and(stubCalls($this->sandbox))->not->toContain('composer lint')
        ->and(directoryContents($this->package))->toBe($this->original);
})->with([
    'an email address without a domain' => ['--author-email=jane', 'Author email: Must be a valid email address.'],
    'a package name without a vendor' => ['--package-name=shop-tools', 'Package name: Package name must be in the format vendor/package.'],
    'a package name with uppercase letters' => ['--package-name=Acme/shop-tools', 'Package name: Package name must be in the format vendor/package.'],
    'a package name with spaces' => ['--package-name=acme/shop tools', 'Package name: Package name must be in the format vendor/package.'],
    'a nested vendor namespace' => ['--vendor-namespace=Acme\\Tools', 'Vendor namespace: Vendor namespace must be a valid PHP namespace.'],
    'a vendor namespace starting with a digit' => ['--vendor-namespace=1Acme', 'Vendor namespace: Vendor namespace must be a valid PHP namespace.'],
    'a class name starting with a digit' => ['--class-name=1Tools', 'Main class name: Class name must be a valid PHP class name.'],
    'a class name with a hyphen' => ['--class-name=Shop-Tools', 'Main class name: Class name must be a valid PHP class name.'],
    'a PHP keyword as class name' => ['--class-name=Class', 'Main class name: Class name must not be a PHP keyword.'],
    'another PHP keyword as class name' => ['--class-name=Match', 'Main class name: Class name must not be a PHP keyword.'],
    'a reserved type name as class name' => ['--class-name=Mixed', 'Main class name: Class name must not be a reserved PHP type name.'],
    'a lowercase reserved type name as class name' => ['--class-name=string', 'Main class name: Class name must not be a reserved PHP type name.'],
]);

it('rejects empty, blank, control-character and invalid UTF-8 values for every field', function (string $option, string $label): void {
    $result = configurePackage($this->package, ['--no-interaction', $option]);

    expect($result->exitCode())->toBe(1)
        ->and(configureResult($result->output()))
        ->toHaveKey('success', false)
        ->toHaveKey('errors', ["{$label}: Enter a non-empty UTF-8 value without control characters."])
        ->and(directoryContents($this->package))->toBe($this->original);
})->with([
    'an empty author name' => ['--author-name=', 'Author name'],
    'a blank author name' => ['--author-name=  ', 'Author name'],
    'an empty author email' => ['--author-email=', 'Author email'],
    'a blank author email' => ['--author-email=   ', 'Author email'],
    'a package name with a tab' => ["--package-name=acme/shop\ttools", 'Package name'],
    'a display name with a newline' => ["--package-name-human=Shop\nTools", 'Package display name'],
    'a description with invalid UTF-8' => ["--package-description=Shop \xFF Tools", 'Package description'],
    'an empty vendor namespace' => ['--vendor-namespace=', 'Vendor namespace'],
    'a blank class name' => ['--class-name= ', 'Main class name'],
]);
