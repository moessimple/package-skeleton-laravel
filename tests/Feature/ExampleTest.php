<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use VendorName\PackageName\Facades\PackageName as PackageNameFacade;
use VendorName\PackageName\PackageName;
use VendorName\PackageName\PackageNameServiceProvider;

it('binds the package class as a singleton', function (): void {
    expect(resolve(PackageName::class))->toBe(resolve(PackageName::class));
});

it('resolves the facade to the bound package class', function (): void {
    expect(PackageNameFacade::getFacadeRoot())->toBe(resolve(PackageName::class));
});

it('merges the package config', function (): void {
    expect(config('package-name.placeholder'))->toBe('default');
});

it('loads the package translations', function (): void {
    expect(trans('package-name::messages.placeholder'))->toBe('PackageName placeholder translation.');
});

it('loads the package views', function (): void {
    expect(view('package-name::placeholder')->render())->toContain('PackageName placeholder view.');
});

it('registers the artisan command', function (): void {
    expect(Artisan::call('package-name:placeholder'))->toBe(0)
        ->and(Artisan::output())->toContain('PackageName placeholder command executed.');
});

it('publishes each resource under its own tag', function (string $tag): void {
    expect(ServiceProvider::pathsToPublish(PackageNameServiceProvider::class, $tag))->not->toBeEmpty();
})->with([
    'package-name-config',
    'package-name-views',
    'package-name-translations',
    'package-name-assets',
    'package-name-migrations',
]);
