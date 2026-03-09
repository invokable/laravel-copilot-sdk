---
name: SDK Sync
description: Automatically tracks official github/copilot-sdk changes and creates PRs to update the Laravel implementation.

on:
  schedule: daily around 5:00 utc+9 on weekdays
  workflow_dispatch:

permissions:
  contents: read
  models: read
  issues: read
  pull-requests: read

engine:
  id: copilot
  agent: laravel-sdk-sync

checkout:
  - path: .
    submodules: recursive
    fetch-depth: 0

tools:
  github:
    mode: remote
    toolsets: [repos, default]
  cache-memory: true

safe-outputs:
  create-pull-request:
    title-prefix: "[sdk-sync] "
    labels: [sdk-sync, automated]
    reviewers: [kawax]
    draft: true
    fallback-as-issue: false
    if-no-changes: ignore

network:
  allowed:
    - defaults
    - php
---

# Official Copilot SDK Sync

You are responsible for keeping this Laravel Copilot SDK package in sync with the official `github/copilot-sdk` repository.

## Pre-check: Avoid Duplicate PRs

Before starting any sync work:

1. Search for open PRs in this repository with the label `sdk-sync`.
2. If an open sdk-sync PR already exists, check whether the official SDK has additional changes beyond what that PR already covers.
3. If no new changes exist beyond the open PR, stop and report "No additional changes to sync" — do not create a duplicate PR.

## Step 1: Detect Changes in the Official SDK

The official SDK is checked out as a git submodule at `./copilot-sdk/`.

1. Record the current submodule commit: `cd copilot-sdk && git rev-parse HEAD`
2. Fetch the latest from origin: `git fetch origin main`
3. Check for new commits: `git log --oneline HEAD..origin/main`
4. If there are no new commits, stop and report "Already up to date."
5. If there are new commits, record the latest commit hash and proceed.

## Step 2: Analyze Changes

Focus on these specific areas of the official SDK:

### Release information
- Check the latest release/tag on `github/copilot-sdk` using GitHub API.

### Source file changes
Compare `HEAD..origin/main` diffs in these key files:

| Official SDK File | What to look for |
|---|---|
| `nodejs/src/generated/rpc.ts` | New RPC methods, changed parameters, new types |
| `nodejs/src/generated/session-events.ts` | New event types, changed event shapes |
| `python/copilot/generated/rpc.py` | Same as rpc.ts but easier to read for PHP mapping |
| `python/copilot/generated/session_events.py` | Same as session-events.ts |
| `nodejs/src/client.ts` | New client methods, API changes |
| `nodejs/src/session.ts` | New session methods, API changes |
| `nodejs/src/types.ts` | New or changed type definitions |

For each changed file, summarize:
- What was added (new methods, types, events)
- What was modified (parameter changes, renamed fields)
- What was removed (deprecated APIs)

## Step 3: Map Changes to Laravel Implementation

Use this mapping table to determine which Laravel files need updates:

| Official SDK | Laravel Implementation |
|---|---|
| `generated/rpc.ts` / `generated/rpc.py` | `src/Types/Rpc/*.php` (type classes), `src/Rpc/*.php` (RPC clients + Pending* classes) |
| `generated/session-events.ts` | `src/Enums/SessionEventType.php`, `src/Types/SessionEvent.php` |
| `client.ts` client methods | `src/Client.php`, `src/Contracts/CopilotClient.php` |
| `session.ts` session methods | `src/Session.php`, `src/Contracts/CopilotSession.php` |
| `types.ts` type definitions | `src/Types/*.php` |
| Protocol version changes | `src/Protocol.php` |

### Implementation conventions

Follow these conventions strictly:

- **Type classes**: Use `readonly class` implementing `Illuminate\Contracts\Support\Arrayable` with `fromArray()` and `toArray()` static/instance methods.
- **RPC types** go in `src/Types/Rpc/` directory.
- **RPC API classes** go in `src/Rpc/` with `Pending*` naming pattern (mirroring Python's `*Api` classes).
- **Enums**: Use PHP 8.4 backed enums in `src/Enums/`.
- **Contracts**: Update interfaces in `src/Contracts/` when adding public methods to Client or Session.
- **Testing**: Update fakes in `src/Testing/` (CopilotFake, FakeSession) when Contracts change.

### What NOT to change

- Do not modify `src/Facades/Copilot.php` unless new facade methods are needed.
- Do not modify `src/CopilotSdkServiceProvider.php` unless new bindings are needed.
- Do not modify `src/Ai/` (Laravel AI SDK integration) unless Copilot's core API changes affect it.
- Do not add Copilot CLI binary bundling.
- Do not change `config/copilot.php` unless new configuration options are genuinely needed.

## Step 4: Implement Changes

1. Update the submodule pointer: `cd copilot-sdk && git checkout origin/main && cd ..`
2. Implement the mapped changes in the Laravel codebase following the conventions above.
3. For each new Type class, ensure it has:
   - `readonly class` declaration
   - Constructor with typed properties
   - `fromArray(array $data): static` static method
   - `toArray(): array` instance method
   - `Arrayable` interface implementation
4. For each new RPC method, ensure:
   - Corresponding `Pending*` class in `src/Rpc/` (if new RPC category)
   - Parameter and result types in `src/Types/Rpc/`
   - Proper method in `ServerRpc.php` or `SessionRpc.php`
5. For each new enum value, add the case to the appropriate PHP enum.

## Step 5: Validate

1. Run the test suite: `composer run test`
2. Run the linter: `composer run lint`
3. If tests fail due to your changes, fix them.
4. If existing tests need updating for new behavior, update them.
5. Add new tests for significant new functionality in the `tests/` directory.

## Step 6: Update Documentation

Review the changes implemented in Step 4 and update relevant documentation files:

### docs/jp/ (Japanese documentation)

Check the following files and update them if the corresponding feature changed:

| Changed area | Document to update |
|---|---|
| New or changed RPC methods | `docs/jp/rpc.md` |
| New or changed session events | `docs/jp/session-event.md` |
| New client/session methods | `docs/jp/basic-usage.md` |
| New or changed session config options | `docs/jp/session-config.md` |
| New or changed streaming behavior | `docs/jp/streaming.md` |
| New or changed model options | `docs/jp/models.md` |
| New or changed MCP features | `docs/jp/mcp.md` |
| New or changed hooks | `docs/jp/hooks.md` |
| New or changed tools | `docs/jp/tools.md` |
| New or changed permission requests | `docs/jp/permission-request.md` |
| New or changed session context | `docs/jp/session-context.md` |
| New or changed resume behavior | `docs/jp/resume.md` |

### docs/getting-started.md (English)

Update if the getting-started flow has changed (new required config, changed API signatures, etc.).

### resources/boost/skills/laravel-copilot-sdk-development/SKILL.md

Update skills for Laravel Boost if necessary.

### Documentation guidelines

- Keep the Japanese tone and style consistent with existing content.
- Only update files that are directly affected by the synced changes.
- If a new feature has no corresponding doc file, skip (do not create new doc files in this workflow).
- Do not rewrite sections unrelated to the synced changes.

## Step 7: Save Sync State

Write the sync state to cache-memory so future runs know what was processed:

```json
{
  "last_synced_commit": "<new commit hash>",
  "synced_at": "<timestamp in YYYY-MM-DD-HH-MM-SS format>",
  "changes_summary": "<brief summary of what changed>"
}
```

Save this as `sdk-sync-state.json` in cache-memory.

## Step 8: Create Pull Request

Create a draft PR with:
- **Title**: A concise description of the sync (e.g., "Sync with copilot-sdk: add new RPC methods for model switching")
- **Body**: Include:
  - The official SDK commit range synced (old..new)
  - Summary of changes detected
  - List of Laravel files added or modified
  - Any breaking changes or migration notes
  - Link to the official SDK comparison: `https://github.com/github/copilot-sdk/compare/<old>..<new>`

## Important Notes

- Python's `rpc.py` is often easier to read than TypeScript's `rpc.ts` for mapping to PHP. Prefer it as reference.
- The `copilot-sdk/` submodule uses the `main` branch of `github/copilot-sdk`.
- Always update the submodule pointer in the PR so the repo tracks the new version.
- If the official SDK introduces a new protocol version, update `src/Protocol.php`.
