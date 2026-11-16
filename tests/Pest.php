<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use VendorName\PackageName\Tests\TestCase;

pest()->extend(TestCase::class)
    ->beforeEach(function (): void {
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
    })
    ->in('Feature', 'Unit');

/* @chisel-configure-tests */
require __DIR__.'/Configure/helpers.php';

// The configure tests start configure.php and its stubbed tools as real processes.
pest()->extend(TestCase::class)->in('Configure');
/* @end-chisel-configure-tests */
