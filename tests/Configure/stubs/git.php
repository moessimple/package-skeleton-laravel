<?php

declare(strict_types=1);

/*
 * Stands in for git while configure.php runs. It records every call in the sandbox call log. Without
 * GIT_USER_NAME or GIT_USER_EMAIL it answers like git without a configured identity.
 */

/** @var list<string> $argv */
$arguments = array_slice($argv, 1);

file_put_contents(
    (string) getenv('CONFIGURE_TEST_CALLS'),
    'git '.implode(' ', $arguments).PHP_EOL,
    FILE_APPEND,
);

$identity = match ($arguments) {
    ['config', 'user.name'] => getenv('GIT_USER_NAME'),
    ['config', 'user.email'] => getenv('GIT_USER_EMAIL'),
    default => null,
};

if ($identity === false) {
    exit(1);
}

if (is_string($identity)) {
    echo $identity.PHP_EOL;
}

exit(0);
