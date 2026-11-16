<?php

declare(strict_types=1);

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process as SymfonyProcess;

/**
 * The skeleton checkout these tests belong to. Tests read from it but never write to it.
 */
function skeletonPath(): string
{
    return dirname(__DIR__, 2);
}

/**
 * Creates an empty directory under the system temp directory with bin/, home/ and tmp/ for one test.
 * bin/ holds the git and gh stubs, so configure.php never reaches the real tools. Each test removes its
 * sandbox in afterEach, so parallel workers and failed tests leave nothing behind.
 */
function createSandbox(): string
{
    $sandbox = sys_get_temp_dir().DIRECTORY_SEPARATOR.'configure-test-'.Str::random(10);

    foreach (['bin', 'home', 'tmp'] as $directory) {
        File::ensureDirectoryExists("{$sandbox}/{$directory}");
    }

    File::put("{$sandbox}/gitconfig", '');

    installStub($sandbox, 'git');
    installStub($sandbox, 'gh');

    return $sandbox;
}

/**
 * Puts a launcher for tests/Configure/stubs/{$name}.php into the sandbox bin/ directory, so that a
 * process finds the stub as $name on its PATH. The launcher is a POSIX shell script on Unix and a batch
 * file on Windows, both calling the PHP binary running the tests.
 */
function installStub(string $sandbox, string $name): void
{
    $stub = __DIR__."/stubs/{$name}.php";

    if (PHP_OS_FAMILY === 'Windows') {
        File::put("{$sandbox}/bin/{$name}.cmd", '@"'.PHP_BINARY.'" "'.$stub.'" %*'.PHP_EOL);

        return;
    }

    File::put("{$sandbox}/bin/{$name}", '#!/bin/sh'.PHP_EOL.'exec '.escapeshellarg(PHP_BINARY).' '.escapeshellarg($stub).' "$@"'.PHP_EOL);
    File::chmod("{$sandbox}/bin/{$name}", 0755);
}

/**
 * Copies the skeleton files git sees (committed, staged and untracked, but not ignored) to $destination.
 * The copy has no vendor/ directory, so configure.php never writes into the skeleton's dependencies.
 * The directory name is part of the scenario because it determines the default package name.
 */
function copySkeleton(string $destination): string
{
    $files = Process::path(skeletonPath())
        ->run(['git', 'ls-files', '-z', '--cached', '--others', '--exclude-standard'])
        ->throw()
        ->output();

    foreach (array_filter(explode("\0", $files)) as $file) {
        if (! is_file(skeletonPath()."/{$file}")) {
            continue;
        }

        File::ensureDirectoryExists(dirname("{$destination}/{$file}"));
        File::copy(skeletonPath()."/{$file}", "{$destination}/{$file}");
    }

    return $destination;
}

/**
 * Runs configure.php in $package as a separate process, the way a package author runs it.
 *
 * $package must be a copy created by copySkeleton() directly inside a sandbox. The process only sees
 * the environment variables listed here plus $env: PATH starts with the sandbox bin/ directory, HOME
 * and TMPDIR point into the sandbox, and git ignores the global and system configuration. Every other
 * inherited variable is removed, so the developer's git identity, GitHub login and agent variables
 * cannot leak into the result.
 *
 * The skeleton's autoloader is prepended instead of installing dependencies in the copy. Pass
 * $withDependencies: false to run configure.php as if `composer install` had not run yet.
 *
 * @param  list<string>  $arguments
 * @param  array<string, string>  $env
 */
function configurePackage(string $package, array $arguments = [], array $env = [], bool $withDependencies = true): ProcessResult
{
    $autoloader = $withDependencies
        ? ['-d', 'auto_prepend_file='.skeletonPath().'/vendor/autoload.php']
        : [];

    return Process::path($package)
        ->env(isolatedEnvironment(dirname($package), $env))
        ->run([PHP_BINARY, ...$autoloader, 'configure.php', ...$arguments]);
}

/**
 * Runs the configurator for $package inside the test process in interactive mode, which a separate
 * process only reaches through a terminal. The test fakes the answers with Prompt::fake() before the
 * call and reads the rendered output through Prompt afterwards.
 *
 * configure.php reads its options from $_SERVER['argv'] and starts git, gh and Composer with the
 * environment of the test process. Both are replaced for the call with the same isolated environment
 * configurePackage() uses, and restored afterwards even when the run throws. Symfony Process only
 * passes on variables that also exist in $_SERVER, so they are set there too.
 *
 * @param  list<string>  $arguments
 * @param  array<string, string>  $env
 */
function configureInteractively(string $package, array $arguments = [], array $env = []): int
{
    require_once skeletonPath().'/configure.php';

    $server = $_SERVER;
    $envGlobal = $_ENV;
    $original = getenv();
    $environment = isolatedEnvironment(dirname($package), $env);

    try {
        $_SERVER['argv'] = ['configure.php', ...$arguments];
        $_ENV = [];

        foreach ($environment as $name => $value) {
            putenv($value === false ? $name : "{$name}={$value}");

            if ($value === false) {
                unset($_SERVER[$name]);
            } else {
                $_SERVER[$name] = $value;
            }
        }

        return new LaravelPackageSkeletonConfigurator($package, interactive: true)->run();
    } finally {
        foreach (array_keys($environment) as $name) {
            putenv(array_key_exists($name, $original) ? "{$name}={$original[$name]}" : $name);
        }

        $_SERVER = $server;
        $_ENV = $envGlobal;
    }
}

/**
 * Prepares configure.php in $package as a process attached to a pseudo terminal, so that standard
 * input is a TTY as in an author's shell. The caller starts it. The Process facade cannot allocate a
 * pseudo terminal, and Windows has none, so tests using this skip Windows. The environment matches
 * configurePackage().
 *
 * @param  list<string>  $arguments
 * @param  array<string, string>  $env
 */
function configureProcessInTerminal(string $package, array $arguments = [], array $env = [], bool $withDependencies = true): SymfonyProcess
{
    $autoloader = $withDependencies
        ? ['-d', 'auto_prepend_file='.skeletonPath().'/vendor/autoload.php']
        : [];

    $process = new SymfonyProcess(
        [PHP_BINARY, ...$autoloader, 'configure.php', ...$arguments],
        $package,
        isolatedEnvironment(dirname($package), $env),
        timeout: 30,
    );

    return $process->setPty(true);
}

/**
 * The environment for a configure.php process: an allowlist of variables, everything else inherited
 * from the test process removed. Symfony Process merges the parent environment into the child unless a
 * variable is set to false. COMPOSER_BINARY points configure.php to the Composer stub, and every stub
 * appends its calls to the sandbox call log.
 *
 * @param  array<string, string>  $env
 * @return array<string, string|false>
 */
function isolatedEnvironment(string $sandbox, array $env = []): array
{
    $inherited = array_fill_keys(
        [...array_keys(getenv()), ...array_filter(array_keys($_ENV), is_string(...))],
        false,
    );

    $allowed = [
        'PATH' => implode(PATH_SEPARATOR, array_filter([
            "{$sandbox}/bin",
            PHP_OS_FAMILY === 'Windows' ? getenv('SystemRoot').'\\System32' : null,
        ])),
        'HOME' => "{$sandbox}/home",
        'TMPDIR' => "{$sandbox}/tmp",
        'GIT_CONFIG_GLOBAL' => "{$sandbox}/gitconfig",
        'GIT_CONFIG_NOSYSTEM' => '1',
        'COMPOSER_BINARY' => __DIR__.'/stubs/composer.php',
        'CONFIGURE_TEST_CALLS' => "{$sandbox}/calls.log",
    ];

    if (PHP_OS_FAMILY === 'Windows') {
        $allowed += [
            'SystemRoot' => (string) getenv('SystemRoot'),
            'TEMP' => "{$sandbox}/tmp",
            'TMP' => "{$sandbox}/tmp",
            'PATHEXT' => (string) getenv('PATHEXT'),
        ];
    }

    return [...$inherited, ...$allowed, ...$env];
}

/**
 * All files below $directory as sorted relative paths with their contents, to prove that a run
 * changed nothing.
 *
 * @return array<string, string>
 */
function directoryContents(string $directory): array
{
    $contents = [];

    foreach (File::allFiles($directory, hidden: true) as $file) {
        $contents[str_replace('\\', '/', $file->getRelativePathname())] = $file->getContents();
    }

    ksort($contents);

    return $contents;
}

/**
 * The calls the stubbed tools received, one line per call in the order they happened.
 *
 * @return list<string>
 */
function stubCalls(string $sandbox): array
{
    if (! File::exists("{$sandbox}/calls.log")) {
        return [];
    }

    return array_values(array_filter(explode(PHP_EOL, File::get("{$sandbox}/calls.log"))));
}

/**
 * Decodes the single JSON line configure.php prints in non-interactive mode.
 *
 * @return array{success: bool, errors: list<string>, summary: array{metadata: array<string, string>, selected_features: list<string>, selected_tools: list<string>, removed_paths: list<string>, modified_files: list<string>, manual_steps: list<string>}}
 */
function configureResult(string $output): array
{
    /** @var array{success: bool, errors: list<string>, summary: array{metadata: array<string, string>, selected_features: list<string>, selected_tools: list<string>, removed_paths: list<string>, modified_files: list<string>, manual_steps: list<string>}} $result */
    $result = json_decode($output, true, flags: JSON_THROW_ON_ERROR);

    return $result;
}
