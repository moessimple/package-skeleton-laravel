<?php

declare(strict_types=1);

/*
 * Stands in for Composer while configure.php runs. It records every call in the sandbox call log and
 * only reproduces the one effect configure.php checks afterwards: `remove` deletes the named packages
 * from require-dev in composer.json. The test decides failures through environment variables.
 */

/** @var list<string> $argv */
$arguments = array_slice($argv, 1);

file_put_contents(
    (string) getenv('CONFIGURE_TEST_CALLS'),
    'composer '.implode(' ', $arguments).PHP_EOL,
    FILE_APPEND,
);

$command = $arguments[0] ?? '';

if ($command === 'lint' && getenv('COMPOSER_LINT_FAILS') === '1') {
    fwrite(STDERR, 'Pint found style issues.'.PHP_EOL);

    exit(1);
}

if ($command === 'dump-autoload' && getenv('COMPOSER_DUMP_FAILS') === '1') {
    fwrite(STDERR, 'Could not write the autoloader.'.PHP_EOL);

    exit(1);
}

if ($command === 'remove') {
    if (getenv('COMPOSER_REMOVE_FAILS') === '1') {
        fwrite(STDERR, 'Could not resolve the dependencies.'.PHP_EOL);

        exit(1);
    }

    if (getenv('COMPOSER_REMOVE_KEEPS_PACKAGES') === '1') {
        exit(0);
    }

    /** @var array{require-dev: array<string, string>} $composer */
    $composer = json_decode((string) file_get_contents('composer.json'), true, flags: JSON_THROW_ON_ERROR);

    foreach (array_filter(array_slice($arguments, 1), fn (string $argument): bool => ! str_starts_with($argument, '-')) as $package) {
        unset($composer['require-dev'][$package]);
    }

    file_put_contents('composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
}

exit(0);
