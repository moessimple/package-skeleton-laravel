<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
/* @chisel-facade */
use VendorName\PackageName\Facades\PackageName as PackageNameFacade;
/* @end-chisel-facade */
use VendorName\PackageName\PackageName;
use VendorName\PackageName\PackageNameServiceProvider;

it('declares the service provider and facade alias for package discovery', function (): void {
    expect(data_get(File::json(dirname(__DIR__, 2).'/composer.json'), 'extra.laravel'))->toBe([
        'providers' => [PackageNameServiceProvider::class],
        'aliases' => ['PackageName' => PackageNameFacade::class],
    ]);
});

it('binds the package class as a singleton', function (): void {
    expect(resolve(PackageName::class))->toBe(resolve(PackageName::class));
});

/* @chisel-facade */
it('resolves the facade to the bound package class', function (): void {
    expect(PackageNameFacade::getFacadeRoot())->toBe(resolve(PackageName::class));
});
/* @end-chisel-facade */

/* @chisel-config */
it('merges the package config', function (): void {
    expect(config('package-name.placeholder'))->toBe('default');
});
/* @end-chisel-config */

/* @chisel-translations */
it('loads the package translations', function (): void {
    expect(trans('package-name::messages.placeholder'))->toBe('PackageName placeholder translation.');
});
/* @end-chisel-translations */

/* @chisel-views */
it('loads the package views', function (): void {
    expect(view('package-name::placeholder')->render())->toContain('PackageName placeholder view.');
});
/* @end-chisel-views */

/* @chisel-commands */
it('registers the artisan command', function (): void {
    expect(Artisan::call('package-name:placeholder'))->toBe(0)
        ->and(Artisan::output())->toContain('PackageName placeholder command executed.');
});
/* @end-chisel-commands */

/* @chisel-publish */
it('publishes each resource under its own tag', function (string $tag): void {
    expect(ServiceProvider::pathsToPublish(PackageNameServiceProvider::class, $tag))->not->toBeEmpty();
})->with([
    /* @chisel-config-publish */
    'package-name-config',
    /* @end-chisel-config-publish */
    /* @chisel-views-publish */
    'package-name-views',
    /* @end-chisel-views-publish */
    /* @chisel-translations-publish */
    'package-name-translations',
    /* @end-chisel-translations-publish */
    /* @chisel-assets-publish */
    'package-name-assets',
    /* @end-chisel-assets-publish */
    /* @chisel-migrations-publish */
    'package-name-migrations',
    /* @end-chisel-migrations-publish */
]);

/* @end-chisel-publish */
