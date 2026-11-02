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
