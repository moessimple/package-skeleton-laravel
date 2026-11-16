<?php

declare(strict_types=1);

namespace VendorName\PackageName;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
/* @chisel-commands */
use VendorName\PackageName\Console\Commands\PackageNameCommand;

/* @end-chisel-commands */

class PackageNameServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-package-name');

        /* @chisel-config */
        $package->hasConfigFile();
        /* @end-chisel-config */

        /* @chisel-views */
        $package->hasViews();
        /* @end-chisel-views */

        /* @chisel-translations */
        $package->hasTranslations();
        /* @end-chisel-translations */

        /* @chisel-assets */
        $package->hasAssets();
        /* @end-chisel-assets */

        /* @chisel-routes */
        $package->hasRoute('package-name');
        /* @end-chisel-routes */

        /* @chisel-migrations */
        $package->hasMigration('create_package_name_placeholder_table');
        /* @end-chisel-migrations */

        /* @chisel-commands */
        $package->hasConsoleCommand(PackageNameCommand::class);
        /* @end-chisel-commands */
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PackageName::class);
    }
}
