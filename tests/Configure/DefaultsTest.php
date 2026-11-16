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

it('selects every feature and tool when no selection flag is given', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);
    $json = configureResult($result->output());

    expect($result->exitCode())->toBe(0)
        ->and($json['success'])->toBeTrue()
        ->and($json['summary']['selected_features'])->toBe(['config', 'routes', 'views', 'translations', 'commands', 'migrations', 'facade', 'assets', 'boost_skill'])
        ->and($json['summary']['selected_tools'])->toBe(['dependabot', 'issue_template', 'funding', 'security_policy']);
});

it('reports the result as exactly one line of JSON with the metadata and a sorted summary', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);

    $lines = explode(PHP_EOL, mb_rtrim($result->output()));
    $json = configureResult($lines[0]);
    $removedPaths = $json['summary']['removed_paths'];
    $modifiedFiles = $json['summary']['modified_files'];
    sort($removedPaths);
    sort($modifiedFiles);

    expect($result->exitCode())->toBe(0)
        ->and($lines)->toHaveCount(1)
        ->and($result->errorOutput())->toBe('')
        ->and($json)->toHaveKeys(['success', 'errors', 'summary'])
        ->and($json['errors'])->toBe([])
        ->and($json['summary'])->toHaveKeys(['metadata', 'selected_features', 'selected_tools', 'removed_paths', 'modified_files', 'manual_steps'])
        ->and($json['summary']['metadata'])->toBe([
            'author_name' => 'Vendor Name',
            'author_email' => 'author@example.com',
            'package_name' => 'vendor-name/laravel-example',
            'package_name_human' => 'Laravel Example',
            'package_description' => 'Laravel Example',
            'vendor_namespace' => 'VendorName',
            'class_name' => 'Example',
        ])
        ->and($json['summary']['removed_paths'])->toContain('configure.php')->toBe($removedPaths)
        ->and($json['summary']['modified_files'])->toContain('composer.json')->toBe($modifiedFiles)
        ->and($json['summary']['manual_steps'])->toBe([
            'Review Dependabot pull requests before merging. This package does not include an automatic merge workflow.',
        ]);
});

it('formats the code, removes the configure dependencies and rebuilds the autoloader through Composer', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0)
        ->and(array_values(array_filter(stubCalls($this->sandbox), fn (string $call): bool => str_starts_with($call, 'composer '))))->toBe([
            'composer lint',
            'composer remove --dev --no-install --no-scripts --no-audit --quiet laravel/agent-detector laravel/chisel laravel/prompts',
            'composer dump-autoload --quiet',
        ])
        ->and(File::json("{$this->package}/composer.json")['require-dev'])->not->toHaveKeys(['laravel/agent-detector', 'laravel/chisel', 'laravel/prompts'])
        ->and("{$this->package}/configure.php")->not->toBeFile();
});
