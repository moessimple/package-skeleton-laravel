<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->sandbox = createSandbox();
    $this->package = copySkeleton("{$this->sandbox}/laravel-example");
});

afterEach(function (): void {
    File::deleteDirectory($this->sandbox);
});

/**
 * Per feature: one file that exists only with the feature, the paths its removal deletes, and the text
 * its removal takes out of files that remain. The text is checked in both directions, so a renamed
 * README heading or provider call cannot let a removal test pass without removing anything.
 *
 * @return array<string, array{string, string, list<string>, array<string, list<string>>}>
 */
function featureRemovals(): array
{
    $provider = 'src/ExampleServiceProvider.php';
    $test = 'tests/Feature/ExampleTest.php';

    return [
        'config' => ['config', 'config/example.php', ['config'], [
            $provider => ['hasConfigFile'],
            $test => ['merges the package config', "'example-config'"],
            'README.md' => ['Publishing the Configuration File', 'example-config'],
            'phpstan.neon.dist' => ['- config'],
            'rector.php' => ["__DIR__.'/config'"],
        ]],
        'routes' => ['routes', 'routes/example.php', ['routes'], [
            $provider => ['hasRoute'],
            'phpstan.neon.dist' => ['- routes'],
            'rector.php' => ["__DIR__.'/routes'"],
        ]],
        'views' => ['views', 'resources/views/placeholder.blade.php', ['resources/views'], [
            $provider => ['hasViews'],
            $test => ['loads the package views', "'example-views'"],
            'README.md' => ['Publishing the Views', 'example-views'],
        ]],
        'translations' => ['translations', 'resources/lang/en/messages.php', ['resources/lang'], [
            $provider => ['hasTranslations'],
            $test => ['loads the package translations', "'example-translations'"],
            'README.md' => ['Publishing the Translations', 'example-translations'],
        ]],
        'commands' => ['commands', 'src/Console/Commands/ExampleCommand.php', ['src/Console', 'tests/Feature/OutsideConsoleTest.php'], [
            $provider => ['hasConsoleCommand', 'ExampleCommand'],
            $test => ['registers the artisan command'],
        ]],
        'migrations' => ['migrations', 'database/migrations/create_example_placeholder_table.php', ['database'], [
            $provider => ['hasMigration'],
            $test => ["'example-migrations'"],
            'README.md' => ['Publishing and Running the Migrations', 'example-migrations'],
            'phpstan.neon.dist' => ['- database'],
            'rector.php' => ["__DIR__.'/database'"],
        ]],
        'facade' => ['facade', 'src/Facades/Example.php', ['src/Facades'], [
            $test => ['resolves the facade', 'Facades\\Example', "'aliases' =>"],
            'composer.json' => ['aliases'],
        ]],
        'assets' => ['assets', 'resources/dist/.gitkeep', ['resources/dist'], [
            $provider => ['hasAssets'],
            $test => ["'example-assets'"],
            'README.md' => ['Publishing the Public Assets', 'example-assets'],
        ]],
        'Boost skill' => ['boost_skill', 'resources/boost/skills/example-development/SKILL.md', ['resources/boost', '.agents/skills/package-generate-skill'], [
            'AGENTS.md' => ['Boost'],
        ]],
    ];
}

it('keeps the files, registrations and documentation of every selected feature', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0);

    foreach (featureRemovals() as [, $file, , $texts]) {
        expect("{$this->package}/{$file}")->toBeFile();

        foreach ($texts as $path => $needles) {
            expect(File::get("{$this->package}/{$path}"))->toContain(...$needles);
        }
    }
});

it('removes a feature that is not selected and keeps the others', function (string $name): void {
    [$feature, $file, $removedPaths, $texts] = featureRemovals()[$name];
    $flags = array_map(
        fn (array $removal): string => '--'.str_replace('_', '-', $removal[0]),
        array_filter(featureRemovals(), fn (array $removal): bool => $removal[0] !== $feature),
    );

    $result = configurePackage($this->package, ['--no-interaction', ...array_values($flags)]);

    expect($result->exitCode())->toBe(0)
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", $removedPaths))->each->not->toBeFile()->not->toBeDirectory()
        ->and(array_keys(array_filter(directoryContents($this->package), fn (string $contents): bool => str_contains($contents, '@chisel') || str_contains($contents, '@end-chisel'))))->toBe([]);

    foreach ($texts as $path => $needles) {
        foreach ($needles as $needle) {
            expect(File::get("{$this->package}/{$path}"))->not->toContain($needle);
        }
    }

    $otherFiles = array_diff(array_column(featureRemovals(), 1), [$file]);

    expect(array_map(fn (string $path): string => "{$this->package}/{$path}", $otherFiles))->each->toBeFile();
})->with(array_keys(featureRemovals()));

it('keeps only the explicitly selected feature and tool and removes the directories left empty', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--security-policy']);
    $json = configureResult($result->output());

    expect($result->exitCode())->toBe(0)
        ->and($json['summary']['selected_features'])->toBe(['config'])
        ->and($json['summary']['selected_tools'])->toBe(['security_policy'])
        ->and("{$this->package}/config/example.php")->toBeFile()
        ->and("{$this->package}/.github/SECURITY.md")->toBeFile()
        ->and("{$this->package}/routes")->not->toBeDirectory()
        ->and("{$this->package}/resources")->not->toBeDirectory()
        ->and("{$this->package}/database")->not->toBeDirectory()
        ->and("{$this->package}/src/Console")->not->toBeDirectory()
        ->and("{$this->package}/src/Facades")->not->toBeDirectory()
        ->and("{$this->package}/.github/workflows")->toBeDirectory()
        ->and("{$this->package}/.github/dependabot.yml")->not->toBeFile()
        ->and("{$this->package}/.github/ISSUE_TEMPLATE")->not->toBeDirectory()
        ->and("{$this->package}/.github/FUNDING.yml")->not->toBeFile()
        ->and($json['summary']['removed_paths'])->toContain('resources', 'database', 'src/Console')
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))
        ->toContain('$package->hasConfigFile();')
        ->not->toContain('hasViews')
        ->not->toContain('hasRoute')
        ->not->toContain('hasMigration')
        ->not->toContain('hasConsoleCommand')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))
        ->toContain("'example-config'")
        ->toContain('merges the package config')
        ->not->toContain("'example-views'")
        ->not->toContain("'example-migrations'")
        ->and(File::get("{$this->package}/README.md"))
        ->toContain('Publishing the Configuration File')
        ->toContain('Security Vulnerabilities')
        ->not->toContain('Publishing the Views')
        ->not->toContain('Publishing and Running the Migrations')
        ->and($json['summary']['manual_steps'])->toBe([]);
});

it('removes the publishing test when no publishable feature is selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--routes', '--commands', '--facade']);

    expect($result->exitCode())->toBe(0)
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))
        ->not->toContain('publishes each resource')
        ->not->toContain('chisel')
        ->toContain('registers the artisan command')
        ->toContain('resolves the facade');
});
