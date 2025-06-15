# AGENTS.md

This file provides guidance to Codex when working with the SureDash WordPress plugin repository.

## Project Overview

SureDash is a WordPress plugin that provides a unified login and dashboard for site users.  The entry point is `suredash.php`, which loads `loader.php` to bootstrap the plugin.  PHP modules live under `core/` and `inc/`, while the React/Redux dashboard and Gutenberg blocks live in `src/`.

### Directory Structure
- `core/` – main PHP modules (CPTs, blocks, integrations, etc.)
- `inc/` – traits, utilities and compatibility code
- `admin/` – PHP for the admin area
- `src/` – JavaScript sources (dashboard app and editor blocks)
- `assets/build/` – compiled JS/CSS from webpack

## Development Environment Setup

1. Install PHP dependencies
   ```bash
   composer install
   ```
2. Install Node dependencies
   ```bash
   npm install
   ```
3. Build assets
   ```bash
   npm run build    # use `npm start` for development
   ```

## Common Commands

### Code Quality
```bash
npm run fixer          # Fix PHP/JS/CSS code style and run static analysis
composer run phpstan   # Additional static analysis
```

### Frontend
```bash
npm start       # Development build with @wordpress/scripts
npm run build   # Production build
```

## Pull Request Guidelines

- PR titles must start with `SD-<issue-number> -` (e.g. `SD-123 - Add new block`).
- Include `[BSF-PR-SUMMARY]` in the PR description so the automatic summary workflow runs.

## Architecture Notes

- PHP classes use the `SureDashboard\` namespace and often implement the `Get_Instance` trait for singletons.
- Gutenberg blocks in `core/blocks/` have matching implementations in `src/editor/`.
- The React dashboard lives in `src/dashboard` with its Redux store in `src/store`.


## Codex Workflow

- Always run `npm run fixer` before committing to fix lint issues and run static analysis.
- After making changes, run `npm run build` and ensure it completes without errors.
