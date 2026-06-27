---
name: SDK Sync
description: Automatically tracks official github/copilot-sdk changes and creates PRs to update the Laravel implementation.

on:
  schedule: # 日本時間で午前5時頃。曜日の指定は英語と1日ずれるので火・木・土。すぐに同期が必要な時は手動実行。
    - cron: weekly on monday around 5:00 utc+9
    - cron: weekly on wednesday around 5:00 utc+9
    - cron: weekly on friday around 5:00 utc+9
  workflow_dispatch:

steps:
    -   name: Detect SDK changes
        id: changes
        env:
            GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        run: |
            cd copilot-sdk
            git fetch origin main --quiet
            CURRENT=$(git rev-parse HEAD)
            LATEST=$(git rev-parse origin/main)
            
            if [ "$CURRENT" = "$LATEST" ]; then
                echo "status=up-to-date" >> $GITHUB_OUTPUT
                exit 0
            fi
            
            # Generate change summary for the agent
            mkdir -p /tmp/gh-aw
            echo "$CURRENT" > /tmp/gh-aw/current-commit.txt
            echo "$LATEST" > /tmp/gh-aw/latest-commit.txt
            
            # Extract release info and key file changes
            git log --oneline "$CURRENT..origin/main" > /tmp/gh-aw/commits.txt
            
            # Check critical files (limit to files that actually matter)
            echo "nodejs/src/generated/rpc.ts nodejs/src/generated/session-events.ts python/copilot/generated/rpc.py nodejs/src/client.ts nodejs/src/session.ts nodejs/src/types.ts" | tr ' ' '\n' | while read file; do
                if git diff "$CURRENT..origin/main" -- "$file" > /tmp/gh-aw/diff-$(echo "$file" | sed 's|/|-|g').txt 2>/dev/null; then
                    [ -s "/tmp/gh-aw/diff-$(echo "$file" | sed 's|/|-|g').txt" ] && echo "changed" || echo "unchanged"
                fi
            done > /tmp/gh-aw/file-status.txt
            
            echo "status=changes-detected" >> $GITHUB_OUTPUT
    
    -   name: Check for duplicate sync PR
        if: steps.changes.outputs.status == 'changes-detected'
        env:
            GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}
            EXPR_GITHUB_REPOSITORY: ${{ github.repository }}
        run: |
            OPEN_PRS=$(gh pr list --repo "$EXPR_GITHUB_REPOSITORY" --label sdk-sync --state open --json number --jq length)
            if [ "$OPEN_PRS" -gt 0 ]; then
                echo "Found $OPEN_PRS open sdk-sync PR(s). Skipping duplicate."
                exit 1
            fi
    
    -   name: Set up PHP (if changes detected)
        if: steps.changes.outputs.status == 'changes-detected'
        uses: shivammathur/setup-php@2.37.2
        with:
            php-version: 8.5
            extensions: mbstring
    -   name: Install Composer dependencies
        run: composer install -q --no-interaction --prefer-dist --optimize-autoloader

permissions:
  contents: read
  models: read
  issues: read
  pull-requests: read

engine:
  id: copilot
  agent: laravel-sdk-sync
  #model: claude-haiku-4.5

checkout:
  - path: .
    submodules: recursive
    fetch-depth: 0

tools:
  github:
    mode: gh-proxy
    toolsets: [repos]
  cache-memory: true

safe-outputs:
  create-pull-request:
    labels: [sdk-sync, copilot]
    reviewers: [kawax]
    draft: true
    fallback-as-issue: true
    if-no-changes: ignore
    signed-commits: false

network:
  allowed:
    - defaults
    - github
    - php
---

# Official Copilot SDK Sync

You are responsible for keeping this Laravel Copilot SDK package in sync with the official `github/copilot-sdk` repository.

## Pre-prepared Data

The previous workflow steps have pre-computed:
- `/tmp/gh-aw/current-commit.txt` — Current submodule commit hash
- `/tmp/gh-aw/latest-commit.txt` — Latest upstream commit hash
- `/tmp/gh-aw/commits.txt` — Commit log (current..latest)
- `/tmp/gh-aw/diff-*.txt` — Diffs for critical files only
- `/tmp/gh-aw/file-status.txt` — Which critical files changed

**Do not re-run git commands to fetch changes; use the pre-computed files above.**

## Step 1: Check for Duplicate PRs (Fast-Track)

Search for open PRs with label `sdk-sync`:
```bash
gh pr list --repo "${{ github.repository }}" --label sdk-sync --state open
```

If an open PR exists, check its commit range. If it already covers the changes in `/tmp/gh-aw/commits.txt`, output "No additional changes to sync" and stop.

## Step 2: Analyze Pre-Computed Changes

Read the change files prepared by the workflow:

1. Load `/tmp/gh-aw/commits.txt` — Review the commit messages to understand what upstream is doing
2. Load each non-empty diff file:
  - `diff-nodejs-src-generated-rpc-ts.txt`
  - `diff-nodejs-src-generated-session-events-ts.txt`
  - `diff-python-copilot-generated-rpc-py.txt` (if available — easier to read than rpc.ts)
  - `diff-nodejs-src-client-ts.txt`
  - `diff-nodejs-src-session-ts.txt`
  - `diff-nodejs-src-types-ts.txt`

3. For each diff:
  - Identify what was added (new methods, types, events)
  - Identify what was modified (parameter changes)
  - Note what was removed (deprecated APIs)

**Do not fetch additional diffs.** The files above are sufficient. If a file is missing from `/tmp/gh-aw/`, it means it did not change.

## Step 3: Map to Laravel Implementation

Using the mapping table below, determine which Laravel files need updates:

| Official SDK | Laravel Implementation |
|---|---|
| `nodejs/src/generated/rpc.ts` / `python/copilot/generated/rpc.py` | `src/Types/Rpc/*.php`, `src/Rpc/*.php` |
| `nodejs/src/generated/session-events.ts` | `src/Enums/SessionEventType.php`, `src/Types/SessionEvent.php` |
| `nodejs/src/client.ts` | `src/Client.php`, `src/Contracts/CopilotClient.php` |
| `nodejs/src/session.ts` | `src/Session.php`, `src/Contracts/CopilotSession.php` |
| `nodejs/src/types.ts` | `src/Types/*.php` |
| Protocol version changes | `src/Protocol.php` |

### Implementation Conventions (Strict)

- **Type classes**: `readonly class` with `Arrayable` interface, `fromArray()`, `toArray()`
- **RPC types**: `src/Types/Rpc/` directory
- **RPC API classes**: `src/Rpc/` with `Pending*` pattern
- **Enums**: PHP 8.4 backed enums in `src/Enums/`
- **Session event data changes only**: **Skip**. The `SessionEvent::$data` is a generic array; skip changes to field shapes inside `data` unless event types themselves changed.
- **Client option key changes**: Update both `CopilotManager::client()` and `CopilotManager::useStdio()` `Arr::only()` filter
- **Do not touch**: `src/Facades/Copilot.php`, `src/CopilotSdkServiceProvider.php`, `src/Ai/`

## Step 4: Implement Changes

1. Update submodule: Read `/tmp/gh-aw/latest-commit.txt` and check out that commit in `copilot-sdk/`
2. Implement mapped Laravel changes (use Python version of rpc.py as reference — easier to read)
3. For new type classes: ensure `readonly`, `Arrayable`, `fromArray()`, `toArray()`. Use `Arr::string()`, `Arr::integer()`, `Arr::float()`, `Arr::array()`, and `Arr::boolean()` where the type is clearly defined.
4. For new RPC methods: add `Pending*` class in `src/Rpc/` and types in `src/Types/Rpc/`

## Step 5: Add Tests

Add Pest tests for all new/changed classes:

- `src/Types/Rpc/*.php` → `tests/Unit/Types/Rpc/*Test.php`
- `src/Types/*.php` → `tests/Unit/Types/*Test.php`
- `src/Rpc/*.php` → `tests/Unit/Rpc/*Test.php`
- `src/Enums/*.php` → `tests/Unit/Enums/*Test.php`

**Minimal test coverage**: Each type should have tests for creation, default values, roundtrip conversion. See existing test files for patterns.

## Step 6: Validate

Run:
```bash
composer run test --compact
composer run lint
```

Fix any failures.

## Step 7: Update Docs (Selective)

Review your changes and update **only affected** docs in `docs/jp/`:
- New/changed RPC methods → `docs/jp/rpc.md`
- New session events → `docs/jp/session-event.md`
- New client/session methods → `docs/jp/basic-usage.md`

**Do not create new doc files.** Only update existing ones if directly affected.

## Step 8: Create Pull Request

Title: "Sync with copilot-sdk: [brief description]" (e.g., "add new RPC methods for model switching")

Body:
- Commit range: `github/copilot-sdk/<old>..<new>`
- Summary of changes
- List of modified Laravel files
- Link: `https://github.com/github/copilot-sdk/compare/<old>..<new>`

## Key Optimization Rules

1. **Use pre-computed diffs only** — Do not fetch git diffs yourself
2. **Skip unchanged files** — If a file is not in `/tmp/gh-aw/`, assume no changes
3. **Shallow analysis** — Focus on structural changes (new methods/types/enums); skip minor tweaks
4. **No broad exploration** — Do not search for "where this might be used" unless it affects the mapping
5. **Minimal testing** — Test only new/modified code; do not refactor or add coverage beyond what's needed
