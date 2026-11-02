<?php

declare(strict_types=1);

namespace VendorName\PackageName\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use VendorName\PackageName\PackageNameServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PackageNameServiceProvider::class,
        ];
    }
}
