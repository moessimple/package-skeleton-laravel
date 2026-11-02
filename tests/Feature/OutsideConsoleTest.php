<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

// Laravel reads APP_RUNNING_IN_CONSOLE once per application, so it has to be set before
// Testbench builds the application for each test.
beforeAll(function (): void {
    $_ENV['APP_RUNNING_IN_CONSOLE'] = 'false';
});

afterAll(function (): void {
    unset($_ENV['APP_RUNNING_IN_CONSOLE']);
});

it('does not register the artisan command outside the console', function (): void {
    expect(app()->runningInConsole())->toBeFalse()
        ->and(Artisan::all())->not->toHaveKey('package-name:placeholder');
});
