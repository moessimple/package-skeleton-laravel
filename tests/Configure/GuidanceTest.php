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

it('replaces the skeleton README and agent guidance with the package templates', function (): void {
    $result = configurePackage($this->package, ['--no-interaction', '--package-name-human=Shop Tools']);

    expect($result->exitCode())->toBe(0)
        ->and("{$this->package}/README_PACKAGE.md")->not->toBeFile()
        ->and("{$this->package}/AGENTS_PACKAGE.md")->not->toBeFile()
        ->and(File::get("{$this->package}/README.md"))->toContain('<h1>Shop Tools</h1>')
        ->not->toContain('This skeleton provides')
        ->and(File::get("{$this->package}/AGENTS.md"))->toStartWith('# Shop Tools')
        ->toContain('This repository is a Laravel package.')
        ->not->toContain('Package Skeleton');
});

it('gives Claude the package guidance and the local skills', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0)
        ->and(File::get("{$this->package}/CLAUDE.md"))->toBe(File::get("{$this->package}/AGENTS.md"))
        ->and("{$this->package}/.claude/skills/package-testing/SKILL.md")->toBeFile();
});

// Creating symbolic links on Windows needs extra privileges. There configure.php copies instead, which
// these tests do not cover.
it('links CLAUDE.md and .claude to the agent guidance instead of copying it', function (): void {
    $result = configurePackage($this->package, ['--no-interaction']);

    expect($result->exitCode())->toBe(0)
        ->and(readlink("{$this->package}/CLAUDE.md"))->toBe('AGENTS.md')
        ->and(readlink("{$this->package}/.claude"))->toBe('.agents');
})->skipOnWindows();
