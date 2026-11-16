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

it('removes the configuration file feature when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--routes', '--views', '--translations', '--commands', '--migrations', '--facade', '--assets', '--boost-skill']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/config")->not->toBeFile()->not->toBeDirectory()
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('hasConfigFile')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('merges the package config')->not->toContain("'example-config'")
        ->and(File::get("{$this->package}/README.md"))->not->toContain('Publishing the Configuration File')->not->toContain('example-config')
        ->and(File::get("{$this->package}/phpstan.neon.dist"))->not->toContain('- config')
        ->and(File::get("{$this->package}/rector.php"))->not->toContain("__DIR__.'/config'")
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['routes/example.php', 'resources/views/placeholder.blade.php', 'resources/lang/en/messages.php', 'src/Console/Commands/ExampleCommand.php', 'database/migrations/create_example_placeholder_table.php', 'src/Facades/Example.php', 'resources/dist/.gitkeep', 'resources/boost/skills/example-development/SKILL.md']))->each->toBeFile();
});

it('removes the routes feature when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--views', '--translations', '--commands', '--migrations', '--facade', '--assets', '--boost-skill']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/routes")->not->toBeFile()->not->toBeDirectory()
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('hasRoute')
        ->and(File::get("{$this->package}/phpstan.neon.dist"))->not->toContain('- routes')
        ->and(File::get("{$this->package}/rector.php"))->not->toContain("__DIR__.'/routes'")
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['config/example.php', 'resources/views/placeholder.blade.php', 'resources/lang/en/messages.php', 'src/Console/Commands/ExampleCommand.php', 'database/migrations/create_example_placeholder_table.php', 'src/Facades/Example.php', 'resources/dist/.gitkeep', 'resources/boost/skills/example-development/SKILL.md']))->each->toBeFile();
});

it('removes the views feature when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--routes', '--translations', '--commands', '--migrations', '--facade', '--assets', '--boost-skill']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/resources/views")->not->toBeFile()->not->toBeDirectory()
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('hasViews')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('loads the package views')->not->toContain("'example-views'")
        ->and(File::get("{$this->package}/README.md"))->not->toContain('Publishing the Views')->not->toContain('example-views')
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['config/example.php', 'routes/example.php', 'resources/lang/en/messages.php', 'src/Console/Commands/ExampleCommand.php', 'database/migrations/create_example_placeholder_table.php', 'src/Facades/Example.php', 'resources/dist/.gitkeep', 'resources/boost/skills/example-development/SKILL.md']))->each->toBeFile();
});

it('removes the translations feature when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--routes', '--views', '--commands', '--migrations', '--facade', '--assets', '--boost-skill']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/resources/lang")->not->toBeFile()->not->toBeDirectory()
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('hasTranslations')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('loads the package translations')->not->toContain("'example-translations'")
        ->and(File::get("{$this->package}/README.md"))->not->toContain('Publishing the Translations')->not->toContain('example-translations')
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['config/example.php', 'routes/example.php', 'resources/views/placeholder.blade.php', 'src/Console/Commands/ExampleCommand.php', 'database/migrations/create_example_placeholder_table.php', 'src/Facades/Example.php', 'resources/dist/.gitkeep', 'resources/boost/skills/example-development/SKILL.md']))->each->toBeFile();
});

it('removes the commands feature when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--routes', '--views', '--translations', '--migrations', '--facade', '--assets', '--boost-skill']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/src/Console")->not->toBeFile()->not->toBeDirectory()
        ->and("{$this->package}/tests/Feature/OutsideConsoleTest.php")->not->toBeFile()->not->toBeDirectory()
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('hasConsoleCommand')->not->toContain('ExampleCommand')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('registers the artisan command')
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['config/example.php', 'routes/example.php', 'resources/views/placeholder.blade.php', 'resources/lang/en/messages.php', 'database/migrations/create_example_placeholder_table.php', 'src/Facades/Example.php', 'resources/dist/.gitkeep', 'resources/boost/skills/example-development/SKILL.md']))->each->toBeFile();
});

it('removes the migrations feature when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--routes', '--views', '--translations', '--commands', '--facade', '--assets', '--boost-skill']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/database")->not->toBeFile()->not->toBeDirectory()
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('hasMigration')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain("'example-migrations'")
        ->and(File::get("{$this->package}/README.md"))->not->toContain('Publishing and Running the Migrations')->not->toContain('example-migrations')
        ->and(File::get("{$this->package}/phpstan.neon.dist"))->not->toContain('- database')
        ->and(File::get("{$this->package}/rector.php"))->not->toContain("__DIR__.'/database'")
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['config/example.php', 'routes/example.php', 'resources/views/placeholder.blade.php', 'resources/lang/en/messages.php', 'src/Console/Commands/ExampleCommand.php', 'src/Facades/Example.php', 'resources/dist/.gitkeep', 'resources/boost/skills/example-development/SKILL.md']))->each->toBeFile();
});

it('removes the facade feature and its alias when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--routes', '--views', '--translations', '--commands', '--migrations', '--assets', '--boost-skill']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/src/Facades")->not->toBeFile()->not->toBeDirectory()
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('resolves the facade')->not->toContain('Facades\\Example')->not->toContain("'aliases' =>")
        ->and(File::get("{$this->package}/composer.json"))->not->toContain('aliases')
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['config/example.php', 'routes/example.php', 'resources/views/placeholder.blade.php', 'resources/lang/en/messages.php', 'src/Console/Commands/ExampleCommand.php', 'database/migrations/create_example_placeholder_table.php', 'resources/dist/.gitkeep', 'resources/boost/skills/example-development/SKILL.md']))->each->toBeFile();
});

it('removes the assets feature when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--routes', '--views', '--translations', '--commands', '--migrations', '--facade', '--boost-skill']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/resources/dist")->not->toBeFile()->not->toBeDirectory()
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('hasAssets')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain("'example-assets'")
        ->and(File::get("{$this->package}/README.md"))->not->toContain('Publishing the Public Assets')->not->toContain('example-assets')
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['config/example.php', 'routes/example.php', 'resources/views/placeholder.blade.php', 'resources/lang/en/messages.php', 'src/Console/Commands/ExampleCommand.php', 'database/migrations/create_example_placeholder_table.php', 'src/Facades/Example.php', 'resources/boost/skills/example-development/SKILL.md']))->each->toBeFile();
});

it('removes the Boost skill and its guidance when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config', '--routes', '--views', '--translations', '--commands', '--migrations', '--facade', '--assets']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/resources/boost")->not->toBeFile()->not->toBeDirectory()
        ->and("{$this->package}/.agents/skills/package-generate-skill")->not->toBeFile()->not->toBeDirectory()
        ->and(File::get("{$this->package}/AGENTS.md"))->not->toContain('Boost')->not->toContain('boost')
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))->not->toContain('chisel')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))->not->toContain('chisel')
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['config/example.php', 'routes/example.php', 'resources/views/placeholder.blade.php', 'resources/lang/en/messages.php', 'src/Console/Commands/ExampleCommand.php', 'database/migrations/create_example_placeholder_table.php', 'src/Facades/Example.php', 'resources/dist/.gitkeep']))->each->toBeFile();
});

it('keeps only the explicitly selected feature and tool', function (): void {
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
        ->and("{$this->package}/.github/dependabot.yml")->not->toBeFile()
        ->and("{$this->package}/.github/ISSUE_TEMPLATE")->not->toBeDirectory()
        ->and("{$this->package}/.github/FUNDING.yml")->not->toBeFile()
        ->and(File::get("{$this->package}/src/ExampleServiceProvider.php"))
        ->toContain('$package->hasConfigFile();')
        ->not->toContain('chisel')
        ->not->toContain('hasViews')
        ->not->toContain('hasRoute')
        ->not->toContain('hasMigration')
        ->not->toContain('hasConsoleCommand')
        ->and(File::get("{$this->package}/tests/Feature/ExampleTest.php"))
        ->toContain("'example-config'")
        ->toContain('merges the package config')
        ->not->toContain('chisel')
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
