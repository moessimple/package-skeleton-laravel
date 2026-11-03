---
name: package-release
description: "Use this skill when preparing Laravel package releases: choosing the version, tags, GitHub releases and their generated release notes, release validation, or release automation changes. Never publish autonomously."
license: MIT
---

# Package Release

## Primary Goal

Prepare a safe package release without tagging, pushing, or publishing unless the user explicitly approves that action.

The GitHub releases are the changelog. There is no `CHANGELOG.md`: workflows only have read access (see `CLAUDE.md`), so no workflow can push a changelog commit after a release.

## Workflow

1. Update `main` with `git pull --ff-only`, run `composer test`, note the tested commit (`git rev-parse HEAD`), and check that the CI checks on that commit are green.
2. Choose the version with SemVer from the pull requests merged since the previous release, or the initial version for the first release. `composer.json` has no `version` field; the Git tag is the version.
3. Preview the generated notes: `gh api repos/{owner}/{repo}/releases/generate-notes -f tag_name=vX.Y.Z -f target_commitish=<commit> --jq .body`. GitHub starts them at the previous release. If a pull request title does not describe its change for users, fix the title before releasing.
4. Draft the release command and ask the user to approve it: `gh release create vX.Y.Z --target <commit> --generate-notes --draft`. The draft creates no tag; publishing it creates the tag on the validated commit, not on whatever `main` points to by then.

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
