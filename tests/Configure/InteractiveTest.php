<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

beforeEach(function (): void {
    $this->sandbox = createSandbox();
    $this->package = copySkeleton("{$this->sandbox}/laravel-example");
});

afterEach(function (): void {
    File::deleteDirectory($this->sandbox);
});

it('asks for every metadata field, the features and the tools and uses the answers', function (): void {
    Prompt::fake([
        Key::ENTER, // Author name
        Key::ENTER, // Author email
        Key::ENTER, // Package name
        Key::ENTER, // Package display name
        ' for testing', Key::ENTER, // Package description
        Key::ENTER, // Vendor namespace
        Key::ENTER, // Main class name
        Key::ENTER, // Package Features
        Key::ENTER, // Package Tools
    ]);

    $exitCode = configureInteractively($this->package);

    expect($exitCode)->toBe(0)
        ->and(File::json("{$this->package}/composer.json"))
        ->toHaveKey('name', 'vendor-name/laravel-example')
        ->toHaveKey('description', 'Laravel Example for testing')
        ->and("{$this->package}/src/ExampleServiceProvider.php")->toBeFile()
        ->and("{$this->package}/configure.php")->not->toBeFile();

    foreach (['Author name', 'Author email', 'Package name', 'Package display name', 'Package description', 'Vendor namespace', 'Main class name', 'Package Features', 'Package Tools'] as $label) {
        Prompt::assertOutputContains($label);
    }
});

it('shows the next steps and a success message after configuring', function (): void {
    Prompt::fake(array_fill(0, 9, Key::ENTER));

    $exitCode = configureInteractively($this->package);

    expect($exitCode)->toBe(0);
    Prompt::assertStrippedOutputContains('Next Steps');
    Prompt::assertStrippedOutputContains('Review Dependabot pull requests before merging.');
    Prompt::assertStrippedOutputContains('Package configured successfully');
    Prompt::assertStrippedOutputDoesntContain('You can start your local development using:');
});

it('shows how to start developing when the Laravel installer passes its directory', function (): void {
    Prompt::fake(array_fill(0, 9, Key::ENTER));

    $exitCode = configureInteractively($this->package, ['--installer-dir=laravel-example']);

    expect($exitCode)->toBe(0);
    Prompt::assertStrippedOutputContains('You can start your local development using:');
    Prompt::assertStrippedOutputContains('cd laravel-example');
    Prompt::assertStrippedOutputContains('Package configured successfully');
});

it('creates a package without optional resources when every feature and tool is deselected', function (): void {
    Prompt::fake([
        ...array_fill(0, 7, Key::ENTER), // Metadata
        Key::CTRL_A, Key::ENTER, // Package Features
        Key::CTRL_A, Key::ENTER, // Package Tools
    ]);

    $exitCode = configureInteractively($this->package);

    expect($exitCode)->toBe(0)
        ->and("{$this->package}/config")->not->toBeDirectory()
        ->and("{$this->package}/routes")->not->toBeDirectory()
        ->and("{$this->package}/resources")->not->toBeDirectory()
        ->and("{$this->package}/database")->not->toBeDirectory()
        ->and("{$this->package}/src/Console")->not->toBeDirectory()
        ->and("{$this->package}/src/Facades")->not->toBeDirectory()
        ->and("{$this->package}/.github/dependabot.yml")->not->toBeFile()
        ->and("{$this->package}/.github/ISSUE_TEMPLATE")->not->toBeDirectory()
        ->and("{$this->package}/.github/FUNDING.yml")->not->toBeFile()
        ->and("{$this->package}/.github/SECURITY.md")->not->toBeFile()
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->not->toContain('->has')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->not->toContain('Facades\\Example')
        ->not->toContain('publishes')
        ->and(File::get("{$this->package}/README.md"))->not->toContain('## Publishing')
        ->not->toContain('## Security Vulnerabilities')
        ->and(File::json("{$this->package}/composer.json"))->not->toHaveKey('extra.laravel.aliases');
    Prompt::assertStrippedOutputDoesntContain('Next Steps');
});

it('shows the validation error and accepts the corrected value', function (): void {
    Prompt::fake([
        Key::ENTER, // Author name
        Key::ENTER, // Author email: the invalid default from the flag
        ...array_fill(0, mb_strlen('not-an-email'), Key::BACKSPACE),
        'jane@example.com', Key::ENTER,
        ...array_fill(0, 7, Key::ENTER), // Remaining metadata, features and tools
    ]);

    $exitCode = configureInteractively($this->package, ['--author-email=not-an-email']);

    expect($exitCode)->toBe(0)
        ->and(File::json("{$this->package}/composer.json"))->toHaveKey('authors.0.email', 'jane@example.com');
    Prompt::assertStrippedOutputContains('Must be a valid email address.');
});

it('shows the error and keeps configure.php when a step fails', function (): void {
    Prompt::fake(array_fill(0, 9, Key::ENTER));

    $exitCode = configureInteractively($this->package, env: ['COMPOSER_LINT_FAILS' => '1']);

    expect($exitCode)->toBe(1)
        ->and("{$this->package}/configure.php")->toBeFile();
    Prompt::assertStrippedOutputContains('Code formatting failed: Pint found style issues.');
    Prompt::assertStrippedOutputDoesntContain('Package configured successfully');
});

it('restores the arguments and environment of the test process after a run', function (): void {
    $argv = $_SERVER['argv'];
    $path = getenv('PATH');
    $composerBinary = getenv('COMPOSER_BINARY');
    $ghUser = getenv('GH_USER');
    Prompt::fake([
        ...array_fill(0, 9, Key::ENTER), // Metadata, features and tools
        Key::ENTER, // Create a GitHub repository now? No
    ]);

    configureInteractively($this->package, ['--installer-dir=laravel-example'], env: ['GH_USER' => 'jane']);

    expect($_SERVER['argv'])->toBe($argv)
        ->and(getenv('PATH'))->toBe($path)
        ->and(getenv('COMPOSER_BINARY'))->toBe($composerBinary)
        ->and(getenv('GH_USER'))->toBe($ghUser);
});
