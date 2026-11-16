<?php

declare(strict_types=1);

/*
 * Stands in for git while configure.php runs. It records every call in the sandbox call log.
 *
 * - Without GIT_USER_NAME or GIT_USER_EMAIL it answers like git without a configured identity.
 * - The working directory counts as a repository only when GIT_INSIDE_WORK_TREE is 1.
 * - There is never an origin remote to remove, as in a fresh repository.
 * - GIT_FAILS names one subcommand, such as init, add, commit, branch or push, that fails.
 * - `add` records the files of the working directory in staged-files.txt next to the call log, so a
 *   test can check what the first commit contains.
 */

/** @var list<string> $argv */
$arguments = array_slice($argv, 1);
$log = (string) getenv('CONFIGURE_TEST_CALLS');

file_put_contents($log, 'git '.implode(' ', $arguments).PHP_EOL, FILE_APPEND);

$options = $arguments;

while (($options[0] ?? null) === '-c') {
    $options = array_slice($options, 2);
}

$subcommand = $options[0] ?? '';

if ($subcommand === getenv('GIT_FAILS')) {
    fwrite(STDERR, "fatal: {$subcommand} failed".PHP_EOL);

    exit(1);
}

if ($options === ['config', 'user.name'] || $options === ['config', 'user.email']) {
    $identity = getenv($options[1] === 'user.name' ? 'GIT_USER_NAME' : 'GIT_USER_EMAIL');

    if ($identity === false) {
        exit(1);
    }

    echo $identity.PHP_EOL;

    exit(0);
}

if ($options === ['rev-parse', '--is-inside-work-tree']) {
    if (getenv('GIT_INSIDE_WORK_TREE') !== '1') {
        fwrite(STDERR, 'fatal: not a git repository (or any of the parent directories): .git'.PHP_EOL);

        exit(128);
    }

    echo 'true'.PHP_EOL;

    exit(0);
}

if ($options === ['remote', 'remove', 'origin']) {
    fwrite(STDERR, "error: No such remote: 'origin'".PHP_EOL);

    exit(2);
}

if ($subcommand === 'add') {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator((string) getcwd(), FilesystemIterator::SKIP_DOTS));

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        $files[] = str_replace('\\', '/', mb_substr($file->getPathname(), mb_strlen((string) getcwd()) + 1));
    }

    sort($files);
    file_put_contents(dirname($log).'/staged-files.txt', implode(PHP_EOL, $files).PHP_EOL);
}

exit(0);
