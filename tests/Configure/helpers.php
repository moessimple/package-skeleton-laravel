<?php

declare(strict_types=1);

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * The skeleton checkout these tests belong to. Tests read from it but never write to it.
 */
function skeletonPath(): string
{
    return dirname(__DIR__, 2);
}

/**
 * Creates an empty directory under the system temp directory with bin/, home/ and tmp/ for one test.
 * Each test removes its sandbox in afterEach, so parallel workers and failed tests leave nothing behind.
 */
function createSandbox(): string
{
    $sandbox = sys_get_temp_dir().DIRECTORY_SEPARATOR.'configure-test-'.Str::random(10);

    foreach (['bin', 'home', 'tmp'] as $directory) {
        File::ensureDirectoryExists("{$sandbox}/{$directory}");
    }

    File::put("{$sandbox}/gitconfig", '');

    return $sandbox;
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
 * The environment for a configure.php process: an allowlist of variables, everything else inherited
 * from the test process removed. Symfony Process merges the parent environment into the child unless a
 * variable is set to false.
 *
 * @param  array<string, string>  $env
 * @return array<array-key, string|false>
 */
function isolatedEnvironment(string $sandbox, array $env = []): array
{
    $inherited = array_fill_keys(
        array_keys([...getenv(), ...$_ENV]),
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
