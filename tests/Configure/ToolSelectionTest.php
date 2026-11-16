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

it('removes Dependabot, its contributing note and its next step when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--issue-template', '--funding', '--security-policy']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/.github/dependabot.yml")->not->toBeFile()
        ->and(File::get("{$this->package}/.github/CONTRIBUTING.md"))->not->toContain('Dependabot')
        ->and(configureResult($result->output())['summary']['manual_steps'])->toBe([])
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['.github/ISSUE_TEMPLATE/bug.yml', '.github/FUNDING.yml', '.github/SECURITY.md']))->each->toBeFile();
});

it('removes the issue template when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--dependabot', '--funding', '--security-policy']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/.github/ISSUE_TEMPLATE")->not->toBeDirectory()
        ->and(configureResult($result->output())['summary']['manual_steps'])->toBe([
            'Review Dependabot pull requests before merging. This package does not include an automatic merge workflow.',
        ])
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['.github/dependabot.yml', '.github/FUNDING.yml', '.github/SECURITY.md']))->each->toBeFile();
});

it('removes the funding file when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--dependabot', '--issue-template', '--security-policy']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/.github/FUNDING.yml")->not->toBeFile()
        ->and(configureResult($result->output())['summary']['manual_steps'])->toBe([
            'Review Dependabot pull requests before merging. This package does not include an automatic merge workflow.',
        ])
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['.github/dependabot.yml', '.github/ISSUE_TEMPLATE/bug.yml', '.github/SECURITY.md']))->each->toBeFile();
});

it('removes the security policy and its README section when it is not selected', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--dependabot', '--issue-template', '--funding']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/.github/SECURITY.md")->not->toBeFile()
        ->and(File::get("{$this->package}/README.md"))->not->toContain('Security Vulnerabilities')
        ->not->toContain('SECURITY.md')
        ->toContain('## Credits')
        ->and(configureResult($result->output())['summary']['manual_steps'])->toBe([
            'Review Dependabot pull requests before merging. This package does not include an automatic merge workflow.',
        ])
        ->and(array_map(fn (string $path): string => "{$this->package}/{$path}", ['.github/dependabot.yml', '.github/ISSUE_TEMPLATE/bug.yml', '.github/FUNDING.yml']))->each->toBeFile();
});

it('selects every tool when only feature flags are given', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--config']);

    expect($result->exitCode())->toBe(0)
        ->and(configureResult($result->output())['summary']['selected_tools'])->toBe(['dependabot', 'issue_template', 'funding', 'security_policy']);
});

it('selects every feature when only tool flags are given', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--funding']);

    expect($result->exitCode())->toBe(0)
        ->and(configureResult($result->output())['summary']['selected_features'])->toBe(['config', 'routes', 'views', 'translations', 'commands', 'migrations', 'facade', 'assets', 'boost_skill'])
        ->and(configureResult($result->output())['summary']['selected_tools'])->toBe(['funding']);
});
