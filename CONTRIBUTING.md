# Contributing to Filament Sticky Save Bar

Thanks for taking the time to contribute! This document explains how to set up the project, the commit conventions we rely on, and how to open a pull request.

This project uses [Conventional Commits](https://www.conventionalcommits.org/) together with [release-please](https://github.com/googleapis/release-please), which **automatically** versions releases and generates the changelog based on your commit messages. Following the commit format below is therefore not optional — it directly determines whether your change ships and what version bump it triggers.

---

## Getting Started

1. **Fork** the repository and clone your fork:

   ```bash
   git clone https://github.com/cocosmos/filament-sticky-save-bar.git
   cd filament-sticky-save-bar
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Create a branch for your work:

   ```bash
   git checkout -b feat/short-description
   ```

### Requirements

- PHP 8.2+
- Filament 5.x

---

## Commit Message Convention

Every commit message **must** follow the Conventional Commits format:

```
<type>(<optional scope>): <description>

[optional body]

[optional footer]
```

The `<type>` prefix is what release-please reads to decide the next version and to build the changelog. **Commits without a valid type are ignored by the release tooling**, so make sure every meaningful change uses one.

### Allowed types

| Type | Use for | Version bump | Appears in changelog |
|---|---|---|---|
| `feat:` | A new feature | **minor** (`1.2.0` → `1.3.0`) | ✅ Features |
| `fix:` | A bug fix | **patch** (`1.2.0` → `1.2.1`) | ✅ Bug Fixes |
| `docs:` | Documentation only changes | none | ✅ Documentation |
| `chore:` | Maintenance, tooling, deps, no user-facing change | none | — |
| `refactor:` | Code change that neither fixes a bug nor adds a feature | none | — |
| `perf:` | A performance improvement | **patch** | ✅ Performance |
| `test:` | Adding or fixing tests | none | — |
| `style:` | Formatting, whitespace, code style (no logic change) | none | — |
| `ci:` | CI/CD configuration changes | none | — |
| `build:` | Build system or dependency packaging changes | none | — |
| `revert:` | Reverting a previous commit | patch | ✅ Reverts |

### Scopes (optional)

A scope adds context in parentheses. Useful scopes for this project:

```
feat(buttons): add Save & Close button
fix(modal): keep bar hidden while a modal is open
feat(translations): add Japanese (ja) translation
docs(readme): document the showOn option
```

### Examples

Good:

```
feat: revert bar to hidden state when all changes are undone
fix(multiselect): detect changes from multiselect fields
docs: add per-page opt-out example
chore(deps): bump filament/filament to 5.1
feat(position)!: make Position::Top the new default
```

Avoid:

```
update stuff
fixed bug
WIP
changes
```

---

## Pull Request Process

1. Keep each PR focused on a single concern. Smaller PRs are reviewed faster.

2. Make sure the project still passes locally before pushing:

3. Push your branch and open a pull request against the `main` branch.

4. **The PR title must be a valid Conventional Commit.** Because PRs are typically squash-merged, the PR title becomes the commit that release-please reads:

   ```
   feat: add discard changes button
   ```

5. Fill in the PR description: what changed, why, and any screenshots for UI changes (this plugin is visual, so before/after images of the bar are very welcome).

6. Link any related issues, e.g. `Closes #12`.

7. A maintainer will review. Address feedback by pushing additional commits to the same branch — no need to force-push unless asked.

Once merged, your change will be picked up by the next release PR automatically. 🎉

---

## Contributing Translations

This plugin ships with translations for several languages. To add or update one:

1. Publish the translation files (or copy an existing one as a starting point):

   ```bash
   php artisan vendor:publish --tag=sticky-save-bar-translations
   ```

2. Add or edit the relevant `sticky-save-bar.php` file under the package's `lang/{locale}/` directory.

3. Keep all keys present (`unsaved_changes`, `save`, `cancel`, `save_and_close`, `discard`).

4. Commit using the translations scope:

   ```
   feat(translations): add Dutch (nl) translation
   ```

---

## Reporting Bugs & Requesting Features

Open an issue with a clear title and, for bugs, steps to reproduce, your PHP and Filament versions, and a screenshot or recording if it's visual. For feature requests, describe the use case rather than just the implementation.

---

## Code of Conduct

Be respectful and constructive. We want this to be a welcoming project for contributors of all experience levels.

---

Thanks again for contributing! ❤️
