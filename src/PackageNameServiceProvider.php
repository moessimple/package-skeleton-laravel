<?php

declare(strict_types=1);

namespace VendorName\PackageName;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VendorName\PackageName\Console\Commands\PackageNameCommand;

class PackageNameServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-package-name')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasAssets()
            ->hasRoute('package-name')
            ->hasMigration('create_package_name_placeholder_table')
            ->hasConsoleCommand(PackageNameCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PackageName::class);
    }
}
