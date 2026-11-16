<?php

declare(strict_types=1);

/*
 * Stands in for the GitHub CLI while configure.php runs. It records every call in the sandbox call log.
 * It is signed in as GH_USER, or signed out when GH_USER is not set.
 */

/** @var list<string> $argv */
$arguments = array_slice($argv, 1);

file_put_contents(
    (string) getenv('CONFIGURE_TEST_CALLS'),
    'gh '.implode(' ', $arguments).PHP_EOL,
    FILE_APPEND,
);

$user = getenv('GH_USER');

if ($user === false) {
    fwrite(STDERR, 'You are not logged into any GitHub hosts.'.PHP_EOL);

    exit(1);
}

if ($arguments === ['api', 'user', '--jq', '.login']) {
    echo $user.PHP_EOL;
}

exit(0);
