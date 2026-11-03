# Contribution Guide

Thank you for considering contributing to Package Name! Please review the following guidelines before submitting a pull request.

For significant changes, please open an issue first so we can discuss the approach.

## Process

1. Fork the project
2. Create a new branch
3. Code, test, commit, and push
4. Open a pull request detailing your changes

## Guidelines

- Apply Rector and Pint fixes by running `composer lint`.
- Ensure `composer test` passes. It requires PHPStan at level max and exactly 100% line and type coverage.
- Give the pull request a title that describes the change for users. The release notes are generated from pull request titles.
- Send a coherent commit history, making sure each commit in your pull request is meaningful.
- You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
- Please remember that we follow [SemVer](http://semver.org/).

## Setup

You need PHP 8.4 or newer, Composer 2.10 or newer (older versions ignore the `policy` config, so `composer test:audit` fails on an abandoned dev dependency), and Xdebug or PCOV for line coverage.

Clone your fork, then install the dev dependencies:

```bash
composer install
```

## Lint

Lint your code:

```bash
composer lint
```

## Tests

Run all tests:

```bash
composer test
```
