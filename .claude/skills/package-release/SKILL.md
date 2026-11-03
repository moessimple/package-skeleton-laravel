---
name: package-release
description: "Use this skill when preparing Laravel package releases: choosing the version, tags, GitHub releases and their generated release notes, release validation, or release automation changes. Never publish autonomously."
license: MIT
---

# Package Release

## Primary Goal

Prepare a safe package release without tagging, pushing, or publishing unless the user explicitly approves that action.

The GitHub releases are the changelog. There is no `CHANGELOG.md`: the ruleset the README recommends for `main` requires status checks, so a workflow cannot push a changelog commit after a release.

## Workflow

1. Validate the release state with `composer test` on an up-to-date `main`, and check that the CI checks on its last commit are green.
2. Choose the version with SemVer from the pull requests merged since the last tag. `composer.json` has no `version` field; the Git tag is the version.
3. Preview the generated notes: `gh api repos/{owner}/{repo}/releases/generate-notes -f tag_name=vX.Y.Z -f previous_tag_name=vA.B.C --jq .body`. If a pull request title does not describe its change for users, fix the title before releasing.
4. Draft the release command for user approval: `gh release create vX.Y.Z --target main --generate-notes --draft`. The draft creates no tag, so the user can review the notes on GitHub before publishing.
5. Do not tag, push, or publish without explicit user approval.

## References

- `.github/CONTRIBUTING.md`
- `composer.json`

## Examples

- Prepare a release by running `composer test`, proposing the version, previewing the generated notes, and drafting the `gh release create` command for user approval.

## Anti-Patterns

- Creating tags, pushing branches, or publishing releases without explicit approval.
- Skipping `composer test` before a release recommendation.
- Adding a `version` field to `composer.json`.
- Adding a workflow that pushes release notes or other commits to `main`.
