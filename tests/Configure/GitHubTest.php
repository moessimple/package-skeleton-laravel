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

it('skips the repository creation with a warning when the GitHub CLI is missing', function (): void {
    File::delete(["{$this->sandbox}/bin/gh", "{$this->sandbox}/bin/gh.cmd"]);
    Prompt::fake(array_fill(0, 9, Key::ENTER));

    $exitCode = configureInteractively($this->package);

    expect($exitCode)->toBe(0)
        ->and(stubCalls($this->sandbox))->each->not->toStartWith('gh ')
        ->and("{$this->package}/configure.php")->not->toBeFile();
    Prompt::assertStrippedOutputContains('GitHub CLI was not found. Repository creation will be skipped.');
    Prompt::assertStrippedOutputDoesntContain('Create a GitHub repository now?');
});

it('skips the repository creation with a warning when the GitHub CLI is not signed in', function (): void {
    Prompt::fake(array_fill(0, 9, Key::ENTER));

    $exitCode = configureInteractively($this->package);

    expect($exitCode)->toBe(0)
        ->and(stubCalls($this->sandbox))->toContain('gh auth status')
        ->not->toContain('gh repo create vendor-name/laravel-example --private --source=. --remote=origin')
        ->and("{$this->package}/configure.php")->not->toBeFile();
    Prompt::assertStrippedOutputContains('GitHub CLI is not authenticated. Repository creation will be skipped.');
});

it('creates no repository when the author declines', function (): void {
    Prompt::fake([
        ...array_fill(0, 9, Key::ENTER), // Metadata, features and tools
        Key::ENTER, // Create a GitHub repository now? No
    ]);

    $exitCode = configureInteractively($this->package, env: ['GH_USER' => 'janedoe']);

    expect($exitCode)->toBe(0)
        ->and(stubCalls($this->sandbox))->each->not->toStartWith('gh repo')
        ->and(stubCalls($this->sandbox))->each->not->toStartWith('git add')
        ->and("{$this->package}/configure.php")->not->toBeFile();
    Prompt::assertStrippedOutputContains('Create a GitHub repository now?');
});

it('creates a private repository, initializes git and pushes the initial commit', function (): void {
    Prompt::fake([
        ...array_fill(0, 9, Key::ENTER), // Metadata, features and tools
        'y', Key::ENTER, // Create a GitHub repository now? Yes
        Key::ENTER, // Repository visibility: Private
    ]);

    $exitCode = configureInteractively($this->package, ['--package-name=acme/laravel-shop-tools', '--author-name=Jane Doe', '--author-email=jane@example.com'], env: ['GH_USER' => 'janedoe']);

    $calls = stubCalls($this->sandbox);

    expect($exitCode)->toBe(0)
        ->and($calls)->toContain(
            'git init',
            'gh repo create acme/laravel-shop-tools --private --source=. --remote=origin',
            'git add --all',
            'git -c user.name=Jane Doe -c user.email=jane@example.com commit -m Initial commit',
            'git branch -M main',
            'git push -u origin main',
        )
        ->and(array_values(array_filter($calls, fn (string $call): bool => str_contains($call, ' commit ') || str_starts_with($call, 'git push'))))->toBe([
            'git -c user.name=Jane Doe -c user.email=jane@example.com commit -m Initial commit',
            'git push -u origin main',
        ])
        ->and("{$this->package}/configure.php")->not->toBeFile();
    Prompt::assertStrippedOutputContains('https://github.com/acme/laravel-shop-tools');
    Prompt::assertStrippedOutputContains('Package configured successfully');
});

it('creates a public repository in an existing git repository without initializing it again', function (): void {
    Prompt::fake([
        ...array_fill(0, 9, Key::ENTER), // Metadata, features and tools
        'y', Key::ENTER, // Create a GitHub repository now? Yes
        Key::DOWN, Key::ENTER, // Repository visibility: Public
    ]);

    $exitCode = configureInteractively($this->package, ['--package-name=acme/laravel-shop-tools'], env: ['GH_USER' => 'janedoe', 'GIT_INSIDE_WORK_TREE' => '1']);

    expect($exitCode)->toBe(0)
        ->and(stubCalls($this->sandbox))->toContain('gh repo create acme/laravel-shop-tools --public --source=. --remote=origin', 'git push -u origin main')
        ->not->toContain('git init');
});

it('commits the package without configure.php and the configure tests', function (): void {
    Prompt::fake([
        ...array_fill(0, 9, Key::ENTER), // Metadata, features and tools
        'y', Key::ENTER, // Create a GitHub repository now? Yes
        Key::ENTER, // Repository visibility: Private
    ]);

    $exitCode = configureInteractively($this->package, env: ['GH_USER' => 'janedoe']);

    expect($exitCode)->toBe(0)
        ->and(stagedFiles($this->sandbox))->toContain('composer.json', 'src/ExampleServiceProvider.php')
        ->not->toContain('configure.php')
        ->each->not->toStartWith('tests/Configure/');
});

it('continues when there is no origin remote to remove', function (): void {
    Prompt::fake([
        ...array_fill(0, 9, Key::ENTER), // Metadata, features and tools
        'y', Key::ENTER, // Create a GitHub repository now? Yes
        Key::ENTER, // Repository visibility: Private
    ]);

    $exitCode = configureInteractively($this->package, env: ['GH_USER' => 'janedoe']);

    expect($exitCode)->toBe(0)
        ->and(stubCalls($this->sandbox))->toContain('git remote remove origin', 'git push -u origin main');
});

it('reports a failed git initialization or repository creation and keeps configure.php', function (string $variable, string $value, string $error): void {
    Prompt::fake([
        ...array_fill(0, 9, Key::ENTER), // Metadata, features and tools
        'y', Key::ENTER, // Create a GitHub repository now? Yes
        Key::ENTER, // Repository visibility: Private
    ]);

    $exitCode = configureInteractively($this->package, env: ['GH_USER' => 'janedoe', $variable => $value]);

    expect($exitCode)->toBe(1)
        ->and(stubCalls($this->sandbox))->each->not->toStartWith('git add')
        ->and("{$this->package}/configure.php")->toBeFile();
    Prompt::assertStrippedOutputContains($error);
    Prompt::assertStrippedOutputDoesntContain('Package configured successfully');
})->with([
    'git init' => ['GIT_FAILS', 'init', 'Git repository initialization failed: fatal: init failed'],
    'gh repo create' => ['GH_CREATE_FAILS', '1', 'GitHub repository creation failed: GraphQL: Name already exists on this account'],
]);

it('reports a failed initial commit or push and restores configure.php', function (string $subcommand): void {
    Prompt::fake([
        ...array_fill(0, 9, Key::ENTER), // Metadata, features and tools
        'y', Key::ENTER, // Create a GitHub repository now? Yes
        Key::ENTER, // Repository visibility: Private
    ]);

    $exitCode = configureInteractively($this->package, env: ['GH_USER' => 'janedoe', 'GIT_FAILS' => $subcommand]);

    expect($exitCode)->toBe(1)
        ->and("{$this->package}/configure.php")->toBeFile()
        ->and(File::get("{$this->package}/configure.php"))->toBe(File::get(skeletonPath().'/configure.php'));
    Prompt::assertStrippedOutputContains("Initial commit push failed: fatal: {$subcommand} failed");
    Prompt::assertStrippedOutputDoesntContain('Package configured successfully');
})->with(['add', 'commit', 'branch', 'push']);
