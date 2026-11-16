<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

beforeEach(function (): void {
    $this->sandbox = createSandbox();
});

afterEach(function (): void {
    File::deleteDirectory($this->sandbox);
});

it('applies explicit metadata to Composer, the classes, the file names, the documents and the summary', function (): void {
    $package = copySkeleton("{$this->sandbox}/laravel-example");

    $result = configurePackage($package, [
        '--no-interaction',
        '--author-name=Jane Doe',
        '--author-email=jane@example.com',
        '--package-name=acme/laravel-shop-tools',
        '--package-name-human=Shop Tools',
        '--package-description=Tools for running a shop.',
        '--vendor-namespace=Acme',
        '--class-name=ShopTools',
    ]);

    expect($result->exitCode())->toBe(0)
        ->and(File::json("{$package}/composer.json"))
        ->toHaveKey('name', 'acme/laravel-shop-tools')
        ->toHaveKey('description', 'Tools for running a shop.')
        ->toHaveKey('homepage', 'https://github.com/acme/laravel-shop-tools')
        ->toHaveKey('keywords', ['laravel', 'acme', 'laravel-shop-tools'])
        ->toHaveKey('authors', [['name' => 'Jane Doe', 'email' => 'jane@example.com', 'role' => 'Developer']])
        ->toHaveKey('autoload.psr-4', ['Acme\\ShopTools\\' => 'src/'])
        ->toHaveKey('autoload-dev.psr-4', [
            'Acme\\ShopTools\\Tests\\' => 'tests/',
            'Workbench\\App\\' => 'workbench/app/',
            'Workbench\\Database\\Factories\\' => 'workbench/database/factories/',
            'Workbench\\Database\\Seeders\\' => 'workbench/database/seeders/',
        ])
        ->toHaveKey('extra.laravel.providers', ['Acme\\ShopTools\\ShopToolsServiceProvider'])
        ->toHaveKey('extra.laravel.aliases', ['ShopTools' => 'Acme\\ShopTools\\Facades\\ShopTools'])
        ->and(File::get("{$package}/src/ShopToolsServiceProvider.php"))
        ->toContain('namespace Acme\\ShopTools;')
        ->toContain('class ShopToolsServiceProvider extends PackageServiceProvider')
        ->toContain("\$package->name('laravel-shop-tools');")
        ->and("{$package}/src/ShopTools.php")->toBeFile()
        ->and("{$package}/src/Facades/ShopTools.php")->toBeFile()
        ->and("{$package}/src/Console/Commands/ShopToolsCommand.php")->toBeFile()
        ->and(File::get("{$package}/README.md"))
        ->toContain('<h1>Shop Tools</h1>')
        ->toContain('Tools for running a shop.')
        ->toContain('composer require acme/laravel-shop-tools')
        ->toContain('[Jane Doe](https://github.com/acme)')
        ->and(File::get("{$package}/LICENSE.md"))->toContain('Copyright (c) Jane Doe')
        ->and(File::get("{$package}/.github/SECURITY.md"))->toContain('send an email to Jane Doe at jane@example.com')
        ->and(configureResult($result->output())['summary']['metadata'])->toBe([
            'author_name' => 'Jane Doe',
            'author_email' => 'jane@example.com',
            'package_name' => 'acme/laravel-shop-tools',
            'package_name_human' => 'Shop Tools',
            'package_description' => 'Tools for running a shop.',
            'vendor_namespace' => 'Acme',
            'class_name' => 'ShopTools',
        ]);
});

it('derives the class, short name, publish tags and migration from the package name', function (string $packageName, string $className, string $shortName, string $tableName): void {
    $package = copySkeleton("{$this->sandbox}/laravel-example");

    $result = configurePackage($package, ['--no-interaction', "--package-name={$packageName}"]);

    expect($result->exitCode())->toBe(0)
        ->and(configureResult($result->output())['summary']['metadata'])
        ->toHaveKey('vendor_namespace', 'Acme')
        ->toHaveKey('class_name', $className)
        ->and("{$package}/src/{$className}ServiceProvider.php")->toBeFile()
        ->and("{$package}/config/{$shortName}.php")->toBeFile()
        ->and("{$package}/routes/{$shortName}.php")->toBeFile()
        ->and("{$package}/resources/boost/skills/{$shortName}-development/SKILL.md")->toBeFile()
        ->and("{$package}/database/migrations/create_{$tableName}_table.php")->toBeFile()
        ->and(File::get("{$package}/database/migrations/create_{$tableName}_table.php"))->toContain("Schema::create('{$tableName}'")
        ->and(File::get("{$package}/README.md"))->toContain("--tag=\"{$shortName}-config\"")
        ->toContain("--tag=\"{$shortName}-migrations\"");
})->with([
    'with the laravel- prefix' => ['acme/laravel-shop-tools', 'ShopTools', 'shop-tools', 'shop_tools_placeholder'],
    'without the laravel- prefix' => ['acme/shop-tools', 'ShopTools', 'shop-tools', 'shop_tools_placeholder'],
]);

it('takes the author from the git identity', function (): void {
    $package = copySkeleton("{$this->sandbox}/laravel-example");

    $result = configurePackage($package, ['--no-interaction'], env: ['GIT_USER_NAME' => 'Jane Doe', 'GIT_USER_EMAIL' => 'jane@example.com']);

    expect($result->exitCode())->toBe(0)
        ->and(configureResult($result->output())['summary']['metadata'])
        ->toHaveKey('author_name', 'Jane Doe')
        ->toHaveKey('author_email', 'jane@example.com')
        ->toHaveKey('package_name', 'jane-doe/laravel-example')
        ->toHaveKey('vendor_namespace', 'JaneDoe');
});

it('falls back to placeholder authors when git is not installed', function (): void {
    $package = copySkeleton("{$this->sandbox}/laravel-example");
    File::delete(["{$this->sandbox}/bin/git", "{$this->sandbox}/bin/git.cmd"]);

    $result = configurePackage($package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0, $result->output())
        ->and(configureResult($result->output())['summary']['metadata'])
        ->toHaveKey('author_name', 'Vendor Name')
        ->toHaveKey('author_email', 'author@example.com')
        ->toHaveKey('package_name', 'vendor-name/laravel-example');
});

it('takes the vendor from the signed-in GitHub user without creating a repository', function (): void {
    $package = copySkeleton("{$this->sandbox}/laravel-example");

    $result = configurePackage($package, ['--no-interaction'], env: ['GH_USER' => 'janedoe', 'GIT_USER_NAME' => 'Jane Doe']);

    expect($result->exitCode())->toBe(0)
        ->and(stubCalls($this->sandbox))->toContain('gh api user --jq .login')
        ->each->not->toStartWith('gh repo create')
        ->and(configureResult($result->output())['summary']['metadata'])
        ->toHaveKey('package_name', 'janedoe/laravel-example')
        ->toHaveKey('vendor_namespace', 'Janedoe')
        ->toHaveKey('author_name', 'Jane Doe');
});

it('takes the vendor from the git identity when the GitHub user cannot be read', function (): void {
    $package = copySkeleton("{$this->sandbox}/laravel-example");

    $result = configurePackage($package, ['--no-interaction'], env: ['GH_USER' => 'janedoe', 'GH_API_FAILS' => '1', 'GIT_USER_NAME' => 'Jane Doe']);

    expect($result->exitCode())->toBe(0)
        ->and(configureResult($result->output())['summary']['metadata'])->toHaveKey('package_name', 'jane-doe/laravel-example');
});

it('names the package after its directory', function (string $directory, string $packageName): void {
    $package = copySkeleton("{$this->sandbox}/{$directory}");

    $result = configurePackage($package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0)
        ->and(configureResult($result->output())['summary']['metadata'])->toHaveKey('package_name', $packageName);
})->with([
    'a package directory' => ['laravel-shop-tools', 'vendor-name/laravel-shop-tools'],
    'the unrenamed skeleton directory' => ['package-skeleton', 'vendor-name/my-package'],
]);

it('prefers explicit metadata over the git identity and the GitHub user', function (): void {
    $package = copySkeleton("{$this->sandbox}/laravel-example");

    $result = configurePackage($package, [
        '--no-interaction',
        '--author-name=John Roe',
        '--author-email=john@example.com',
        '--package-name=acme/laravel-shop-tools',
    ], env: ['GH_USER' => 'janedoe', 'GIT_USER_NAME' => 'Jane Doe', 'GIT_USER_EMAIL' => 'jane@example.com']);

    expect($result->exitCode())->toBe(0)
        ->and(configureResult($result->output())['summary']['metadata'])
        ->toHaveKey('author_name', 'John Roe')
        ->toHaveKey('author_email', 'john@example.com')
        ->toHaveKey('package_name', 'acme/laravel-shop-tools');
});

it('keeps quotes, backslashes and umlauts intact in the generated JSON, YAML and Markdown', function (): void {
    $package = copySkeleton("{$this->sandbox}/laravel-example");
    $authorName = 'Jöhn "JD" O\'Doe \\ Søn';
    $description = 'Builds "quoted" C:\\paths for Grüße.';

    $result = configurePackage($package, [
        '--no-interaction',
        "--author-name={$authorName}",
        "--package-description={$description}",
        '--package-name=acme/laravel-shop-tools',
    ]);

    /** @var array{metadata: array{author: string}} $skill */
    $skill = Yaml::parse(explode('---', File::get("{$package}/resources/boost/skills/shop-tools-development/SKILL.md"))[1]);

    expect($result->exitCode())->toBe(0)
        ->and(File::json("{$package}/composer.json"))
        ->toHaveKey('description', $description)
        ->toHaveKey('authors.0.name', $authorName)
        ->and($skill['metadata']['author'])->toBe($authorName)
        ->and(File::get("{$package}/README.md"))->toContain($description)
        ->toContain("[{$authorName}]")
        ->and(configureResult($result->output())['summary']['metadata'])
        ->toHaveKey('author_name', $authorName)
        ->toHaveKey('package_description', $description);
});
