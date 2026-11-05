<?php

declare(strict_types=1);

// Turns this template into a new package: asks for the package details, replaces the skeleton's
// names in every tracked file and path, deletes itself, then installs the
// dependencies, applies the lint fixes and runs the quality gate.

const USAGE = <<<'TXT'
    Usage: php configure.php [options]

    Asks for each value, suggesting the option or the default. With --no-interaction it uses them
    without asking.

      --vendor=acme                 Composer vendor and GitHub owner (default: from the origin remote)
      --package=laravel-example     Composer package name (default: from the origin remote)
      --namespace=Acme              Vendor namespace (default: vendor in StudlyCase)
      --class=LaravelExample        Package namespace and class name (default: package in StudlyCase)
      --title="Laravel Example"     Human-readable package name (default: package in Title Case)
      --author-name="Jane Doe"      Author name (default: git config user.name)
      --author-email=jane@acme.dev  Author email (default: git config user.email)
      -n, --no-interaction          Use the options and defaults without asking
      -h, --help                    Show this help

    TXT;

$options = getopt('nh', ['no-interaction', 'help', 'vendor:', 'package:', 'namespace:', 'class:', 'title:', 'author-name:', 'author-email:']);

if (isset($options['h']) || isset($options['help'])) {
    echo USAGE;

    exit(0);
}

$interactive = ! isset($options['n']) && ! isset($options['no-interaction']);

$say = function (string $message): void {
    echo "{$message}\n";
};

$fail = function (string $message): never {
    fwrite(STDERR, "\nconfigure.php: {$message}\n");

    exit(1);
};

$ask = function (string $question, string $default): string {
    $answer = readline($default === '' ? "  {$question}: " : "  {$question} [{$default}]: ");

    return $answer === false || mb_trim($answer) === '' ? $default : mb_trim($answer);
};

$option = fn (string $name, string $default, string $question): string => $interactive
    ? $ask($question, is_string($options[$name] ?? null) ? $options[$name] : $default)
    : (is_string($options[$name] ?? null) ? $options[$name] : $default);

// cmd.exe does not run a command whose redirect target it cannot open, such as /dev/null.
$nullDevice = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';

$git = fn (string $command): string => mb_trim((string) shell_exec("git {$command} 2>{$nullDevice}"));

$studly = fn (string $slug): string => str_replace(' ', '', ucwords(str_replace(['-', '_', '.'], ' ', $slug)));

$run = function (string $command, string $purpose) use ($say, $fail): void {
    $say("\n==> {$purpose}\n    Running: {$command}\n");

    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        $fail("'{$command}' failed with exit code {$exitCode}. The package is already renamed: fix the problem, then run the remaining steps yourself (composer install, composer lint, composer test). To undo the renaming instead, run: git restore . && git clean -fd");
    }
};

// Checked before any file changes, although only composer test, the last step, needs it.
if (! extension_loaded('xdebug') && ! extension_loaded('pcov')) {
    $fail('composer test needs Xdebug or PCOV for line coverage. Install one of them, then run the script again.');
}

exec("composer --version 2>{$nullDevice}", $composerVersion, $exitCode);

if ($exitCode !== 0) {
    $fail('Composer is not available as the composer command. Install it from https://getcomposer.org, then run the script again.');
}

// composer.json requires Composer 2.11 (composer-plugin-api), so fail early with a clear message.
if (preg_match('/Composer version (\d+\.\d+\.\d+)/', implode("\n", $composerVersion), $match) === 1 && version_compare($match[1], '2.11.0', '<')) {
    $fail("composer test needs Composer 2.11 or newer, found {$match[1]}. Run composer self-update, then run the script again.");
}

chdir(__DIR__);

$files = array_filter(explode("\n", $git('ls-files')));

if ($files === []) {
    $fail('No tracked files found. Run the script inside the repository you created from the template.');
}

if ($git('status --porcelain') !== '') {
    $fail('The working tree has uncommitted changes. Commit or stash them first, so a failed run can be undone with git.');
}

preg_match('~[/:]([^/:]+)/([^/]+?)(?:\.git)?$~', $git('remote get-url origin'), $remote);

if ($interactive) {
    $say("This script turns the template into your package. Press Enter to accept a suggestion.\n");
}

$vendor = $option('vendor', $remote[1] ?? '', 'Composer vendor (GitHub owner)');
$package = $option('package', $remote[2] ?? '', 'Composer package name');

// laravel-package-tools derives the config file, the view and translation namespaces and the
// publish tags from Package::shortName(), which is everything after the first 'laravel-'.
$position = mb_strpos($package, 'laravel-');
$shortName = $position === false ? $package : mb_substr($package, $position + mb_strlen('laravel-'));

$namespace = $option('namespace', $studly($vendor), 'Vendor namespace');
$class = $option('class', $studly($shortName), 'Package namespace and class name');
$title = $option('title', ucwords(str_replace(['-', '_'], ' ', $package)), 'Package title');
$authorName = $option('author-name', $git('config user.name'), 'Author name');
$authorEmail = $option('author-email', $git('config user.email'), 'Author email');

if (preg_match('/^[a-z0-9]([_.-]?[a-z0-9]+)*$/', $vendor) !== 1) {
    $fail("The vendor must be a lowercase Composer name segment such as 'acme', got '{$vendor}'.");
}

// The package name becomes the config file name, and config() reads a dot as a nested key.
if (preg_match('/^[a-z0-9]([_-]?[a-z0-9]+)*$/', $package) !== 1) {
    $fail("The package must be a lowercase Composer name segment without dots such as 'laravel-example', got '{$package}'.");
}

if ("{$vendor}/{$package}" === 'moessimple/package-skeleton-laravel') {
    $fail('The package name is the name of this template. Create your repository with "Use this template", or pass --vendor and --package.');
}

foreach (['namespace' => $namespace, 'class' => $class] as $name => $value) {
    if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $value) !== 1) {
        $fail("The {$name} must be a StudlyCase PHP identifier such as 'Acme', got '{$value}'.");
    }
}

if ($title === '' || $authorName === '') {
    $fail('The title and the author name must not be empty.');
}

// All three are written unescaped into composer.json, where a quote or backslash breaks the JSON.
// FILTER_VALIDATE_EMAIL accepts both in a quoted local part such as "a\\b"@example.com.
if (strpbrk($title.$authorName.$authorEmail, '"\\') !== false) {
    $fail('The title, the author name and the author email must not contain double quotes or backslashes.');
}

if (filter_var($authorEmail, FILTER_VALIDATE_EMAIL) === false) {
    $fail("The author email must be an email address, got '{$authorEmail}'.");
}

// strtr() replaces the longest matching placeholder first, so laravel-package-name wins over
// package-name and the JSON-escaped namespace over the plain one.
$replacements = [
    'VendorName\\\\PackageName' => "{$namespace}\\\\{$class}",
    'VendorName\\PackageName' => "{$namespace}\\{$class}",
    'PackageName' => $class,
    'vendor-name' => $vendor,
    'laravel-package-name' => $package,
    'package-name' => $shortName,
    'package_name' => str_replace('-', '_', $shortName),
    'Package Name' => $title,
    'Author Name' => $authorName,
    'author@example.com' => $authorEmail,
];

$say(<<<TXT

    The script will now:

      1. Rename the package to {$vendor}/{$package} with the namespace {$namespace}\\{$class},
         the config key and publish tag prefix {$shortName}, the title "{$title}" and the
         author {$authorName} <{$authorEmail}>, in the contents and names of all tracked files.
      2. Replace the template header and instructions in README.md with the package header, and
         delete configure.php and the workflow that tests it.
      3. Install the dependencies (composer install).
      4. Apply the Rector and Pint fixes the renaming needs, such as re-sorted imports (composer lint).
      5. Run the quality gate: lint, type coverage, static analysis and tests (composer test).

    TXT);

if ($interactive && ! in_array(mb_strtolower($ask('Continue? (y/n)', 'y')), ['y', 'yes'], true)) {
    $fail('Aborted, nothing was changed.');
}

$say("\n==> Renaming the package");

$templateFiles = ['configure.php', '.github/workflows/configure.yml'];

foreach (array_diff($files, $templateFiles) as $file) {
    $original = (string) file_get_contents($file);

    $contents = match ($file) {
        'README.md' => (string) preg_replace(
            ['/<!-- template:start -->.*?<!-- template:end -->\n\n/s', '/<!-- package-header:start\n|package-header:end -->\n/'],
            '',
            $original,
        ),
        'rector.php' => (string) preg_replace('/^.*(UnlinkFuncCallToFileFacadeDeleteRector|configure\.php).*\n/m', '', $original),
        'phpstan.neon.dist' => (string) preg_replace('/^.*configure\.php\n/m', '', $original),
        default => $original,
    };

    $contents = strtr($contents, $replacements);

    if ($contents !== $original) {
        file_put_contents($file, $contents);
        $say("    Updated  {$file}");
    }

    $target = strtr($file, $replacements);

    if ($target !== $file) {
        if (! is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }

        rename($file, $target);
        $say("    Renamed  {$file} -> {$target}");

        // The Boost skill lives in a directory named after the package, which is now empty.
        for ($directory = dirname($file); $directory !== '.' && is_dir($directory) && scandir($directory) === ['.', '..']; $directory = dirname($directory)) {
            rmdir($directory);
        }
    }
}

$say("\n==> Removing the template tooling");

foreach ($templateFiles as $file) {
    unlink($file);
    $say("    Deleted  {$file}");
}

$run('composer install --no-interaction', 'Installing the dependencies');
$run('composer lint', 'Applying the Rector and Pint fixes the renaming needs');
$run('composer test', 'Running the quality gate');

$say(<<<TXT

    Done. {$vendor}/{$package} is configured and passes composer test.

    Review the changes with git status and commit them. GitHub does not copy repository settings
    from a template: add a ruleset for main that requires a pull request before merging and
    the status checks lint, static and tests.

    TXT);
