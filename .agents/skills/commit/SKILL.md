---
name: commit
description: Prepare or execute authorized commits for this project using its mandatory English Conventional Commits format. Use when writing a commit message, preparing a commit, or committing project changes.
---

# Project commits

## Required format

Use `<type>(<scope>): <description>`. The type, scope, description, body, and footers must all be in English.

Every commit must have a lowercase English scope identifying its area, such as `auth`, `login`, `figma`, `assets`, `mvp`, or `project`.

Allowed types:

- `feat`: new feature or product asset; `fix`: defect correction.
- `docs`: documentation, specifications, or design records.
- `style`: formatting only; `refactor`: code reorganization; neither changes behavior.
- `perf`: performance improvement; `test`: added or corrected tests.
- `build`: build system or dependencies; `ci`: continuous integration or automation.
- `chore`: project maintenance or configuration; `revert`: reversal of a previous change.

Write a specific imperative description with no final period. Target a header of at most 72 characters. Avoid vague descriptions such as `update` or `changes`.

Use a body or footers only when needed, in English, explaining why the change is necessary. Use `!` or `BREAKING CHANGE` only for a real compatibility break.

Never mention Codex, OpenAI, AI, or assistants in commit messages. Never add a `Co-Authored-By` line.

Choose scopes by result: `docs(mvp)` for specifications, `docs(figma)` for design records, `feat(assets)` for new logos or icons, `feat(auth)` or `feat(login)` for authentication, and `chore(project)` for configuration.

```text
feat(auth): add email and password login
docs(mvp): define supported sign-in methods
```

## Coherent commits and partial staging

Group changes into commits with one coherent purpose, each independently reviewable and revertible. Split unrelated changes, including changes within the same file when necessary. Do not split arbitrarily by file, line, type, or a fixed commit count. Keep each feature or fix with the tests, documentation, configuration, and assets it needs to avoid broken intermediate commits.

For tracked files, use `git add -p -- path` to select relevant hunks or lines. Use `s` to split a hunk or `e` to edit the patch for inclusion only; do not change the actual working-tree content while selecting changes.

For a new file that needs partial staging, first use `git add -N -- path`, then `git add -p -- path`. For an entire new file belonging to the commit, use explicit `git add -- path`.

## Review and authorization

Stage or commit only when authorized. Creating or changing files does not authorize an automatic commit; requesting a message alone does not authorize staging or committing.

Before each commit, review `git status`, `git diff`, and `git diff --staged`. Confirm the staged group is self-contained and includes only authorized scope, with no sensitive data or unrelated changes, including others' work.

After each commit, check `git status` and preserve all remaining work. Do not push, amend, reset, or rebase without a request authorizing that action.
