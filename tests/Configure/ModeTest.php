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

it('rejects interactive configuration on an unsupported platform before changing files', function (): void {
    $original = directoryContents($this->package);
    $script = <<<'PHP'
        require $argv[1].'/vendor/autoload.php';
        require $argv[1].'/configure.php';

        $package = $argv[2];
        $_SERVER['argv'] = ['configure.php'];
        Laravel\Prompts\Prompt::fake(array_fill(0, 9, Laravel\Prompts\Key::ENTER));

        $configurator = new class($package, interactive: true) extends LaravelPackageSkeletonConfigurator
        {
            protected function supportsInteractivePrompts(): bool
            {
                return false;
            }
        };

        exit($configurator->run());
        PHP;

    $result = Process::path($this->package)
        ->env(isolatedEnvironment($this->sandbox))
        ->run([PHP_BINARY, '-r', $script, skeletonPath(), $this->package]);

    expect($result->exitCode())->toBe(1)
        ->and($result->output())->toBe('')
        ->and($result->errorOutput())->toContain('Interactive configuration is not supported on native Windows.')
        ->toContain('WSL')
        ->toContain('php configure.php --no-interaction --package-name=acme/laravel-example')
        ->and(directoryContents($this->package))->toBe($original)
        ->and(stubCalls($this->sandbox))->toBe([]);
});

it('rejects interactive configuration on native Windows before changing files', function (): void {
    $original = directoryContents($this->package);
    $script = <<<'PHP'
        require $argv[1].'/vendor/autoload.php';
        require $argv[1].'/configure.php';

        $package = $argv[2];
        $_SERVER['argv'] = ['configure.php'];

        exit(new LaravelPackageSkeletonConfigurator($package, interactive: true)->run());
        PHP;

    $result = Process::path($this->package)
        ->env(isolatedEnvironment($this->sandbox))
        ->run([PHP_BINARY, '-r', $script, skeletonPath(), $this->package]);

    expect($result->exitCode())->toBe(1)
        ->and($result->output())->toBe('')
        ->and($result->errorOutput())->toContain('Interactive configuration is not supported on native Windows.')
        ->and(directoryContents($this->package))->toBe($original)
        ->and(stubCalls($this->sandbox))->toBe([]);
})->skip(PHP_OS_FAMILY !== 'Windows', 'Requires native Windows.');

// Tests that need an interactive standard input run in a pseudo terminal, which Windows does not offer.

it('runs without prompts and prints one JSON line when standard input is not a terminal', function (): void {
    $result = configurePackage($this->package);

    expect($result->exitCode())->toBe(0)
        ->and(explode(PHP_EOL, mb_rtrim($result->output())))->toHaveCount(1)
        ->and(configureResult($result->output())['success'])->toBeTrue();
});

it('runs without prompts and prints one JSON line in a terminal when the no-interaction flag is given', function (string $flag): void {
    $process = configureProcessInTerminal($this->package, [$flag]);

    $process->run();

    expect($process->getExitCode())->toBe(0)
        ->and(explode("\n", mb_rtrim($process->getOutput())))->toHaveCount(1)
        ->and(configureResult($process->getOutput())['success'])->toBeTrue();
})->with(['--no-interaction', '-n'])->skipOnWindows();

it('runs without prompts and prints one JSON line in a terminal when the environment signals no interaction', function (string $variable, string $value): void {
    $process = configureProcessInTerminal($this->package, env: [$variable => $value]);

    $process->run();

    expect($process->getExitCode())->toBe(0)
        ->and(explode("\n", mb_rtrim($process->getOutput())))->toHaveCount(1)
        ->and(configureResult($process->getOutput())['success'])->toBeTrue();
})->with([
    'Composer without interaction' => ['COMPOSER_NO_INTERACTION', '1'],
    'a detected coding agent' => ['AI_AGENT', 'example-agent'],
])->skipOnWindows();

it('asks for the metadata in a terminal without a non-interactive signal', function (): void {
    $process = configureProcessInTerminal($this->package);

    $process->start();
    $process->waitUntil(fn (string $type, string $output): bool => str_contains($output, 'Used in composer.json credits and README attribution.'));
    $process->stop();

    expect($process->getOutput())->toContain('Configure your Laravel package')
        ->toContain('Author name')
        ->toContain('Used in composer.json credits and README attribution.')
        ->not->toContain('"success"')
        ->and("{$this->package}/configure.php")->toBeFile();
})->skipOnWindows();

it('rejects an invalid option with the usage on the terminal without a non-interactive signal', function (): void {
    $process = configureProcessInTerminal($this->package, ['--unknown-option']);

    $process->run();

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('The "--unknown-option" option does not exist.')
        ->and($process->getOutput())->toContain('Usage: php configure.php [options]')
        ->not->toContain('"success"');
})->skipOnWindows();

it('rejects an invalid option as a JSON error in a terminal when a coding agent runs it', function (): void {
    $process = configureProcessInTerminal($this->package, ['--unknown-option'], env: ['AI_AGENT' => 'example-agent']);

    $process->run();

    expect($process->getExitCode())->toBe(1)
        ->and(configureResult($process->getOutput()))->toHaveKey('errors', ['The "--unknown-option" option does not exist.']);
})->skipOnWindows();

it('reports missing dependencies on standard error in a terminal without a non-interactive signal', function (): void {
    $process = configureProcessInTerminal($this->package, withDependencies: false);

    $process->run();

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('Composer dependencies are not installed. Run `composer install` before `php configure.php`.')
        ->and($process->getOutput())->toBe('');
})->skipOnWindows();
