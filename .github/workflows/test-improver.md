---
name: Test Improver
description: Weekly workflow to improve test coverage incrementally by adding missing tests.

on:
  schedule: weekly on saturday around 6:00 utc+9 # 日本時間で日曜午前6時頃
  workflow_dispatch:

steps:
  - name: Set up PHP
    uses: shivammathur/setup-php@v2
    with:
      php-version: 8.5
      extensions: mbstring
      coverage: xdebug

  - name: Install Composer dependencies
    run: composer install --no-interaction --prefer-dist --optimize-autoloader

permissions:
  contents: read
  models: read
  pull-requests: read
  issues: read

engine:
  id: copilot
  model: claude-sonnet-4.6

checkout:
  - path: .
    submodules: recursive

tools:
  github:
    mode: remote
    toolsets: [default]
  cache-memory: true

safe-outputs:
  create-pull-request:
    title-prefix: "[test-improver] "
    labels: [test-improver, automated]
    reviewers: [kawax]
    draft: true
    expires: 14d
    fallback-as-issue: false
    if-no-changes: ignore

network:
  allowed:
    - defaults
    - php
---

# Test Improver

You are responsible for incrementally improving test coverage in this Laravel Copilot SDK package.
This workflow runs weekly. Make small, focused changes — typically one source file's tests per run.

## Pre-check: Avoid Duplicate PRs

Before starting any work:

1. Search for open PRs in this repository with the label `test-improver`.
2. If an open test-improver PR already exists, stop and report "An open test-improver PR already exists. Skipping this run."

## Step 1: Check for sdk-sync Test Gaps (Priority 1)

Check if recently merged `sdk-sync` PRs introduced source files without corresponding tests.

1. Read the last processed PR number from cache-memory (key: `test-improver-state`).
2. Search for merged PRs with the label `sdk-sync`, sorted by most recently merged.
3. For each new PR (merged after the last processed one):
   - Review the PR diff to identify newly added files under `src/`.
   - For each new `src/` file, check if a corresponding test file exists under `tests/`.
   - Mapping convention: `src/Types/Foo.php` → `tests/Unit/Types/FooTest.php`, `src/Rpc/PendingFoo.php` → `tests/Unit/Rpc/PendingFooTest.php`, etc.
4. If test gaps are found, add tests for **all** newly added files from those PRs (this is the exception to the one-file-at-a-time rule).
5. If no sdk-sync PRs have new untested files, or if the testing has already been improved in this workflow, proceed to Step 2.

## Step 2: Coverage-Based Improvement (Priority 2)

Only execute this step if Step 1 found no work to do.

1. Run the coverage command:
   ```bash
   vendor/bin/pest --no-progress --coverage --parallel
   ```
2. Parse the output to identify files with the lowest coverage percentages.
3. Read the previously improved files list from cache-memory (key: `test-improver-state`) and skip those.
4. Select **one** source file to improve, preferring the lowest coverage that is not in the skip list.

### Files to Always Skip

These files are difficult to test in isolation due to external process or runtime dependencies. Always skip them:

- `src/Transport/StdioTransport.php` — requires real process stdio streams
- `src/Process/ProcessWrapper.php` — requires real process execution
- `src/CopilotSdkServiceProvider.php` — service provider integration is already covered
- `src/Ai/CopilotGateway.php` — depends on Laravel AI SDK runtime
- `src/Ai/CopilotProvider.php` — depends on Laravel AI SDK runtime
- `src/Facades/Copilot.php` — facade is tested through feature tests
- `src/helpers.php` — helper function is tested in feature tests

If the selected file appears too difficult to test meaningfully (e.g., requires complex I/O mocking that would result in brittle tests), skip it and choose the next lowest-coverage file.

## Step 3: Study Existing Test Patterns

Before writing tests, study existing tests to match the project's conventions:

1. Read 2-3 existing test files that are similar to the target (same directory or similar class type).
2. Note the following patterns:
   - **Framework**: Pest PHP with `describe()`/`it()` BDD-style syntax
   - **Assertions**: Fluent `expect()` API with chained `.toBe()`, `.toBeInstanceOf()`, `.toHaveKey()`, etc.
   - **Mocking**: Mockery for dependency injection mocking (`Mockery::mock()`, `shouldReceive()`)
   - **Structure**: Unit tests in `tests/Unit/` mirroring `src/` directory structure
   - **Type tests**: For `readonly class` types, test `fromArray()`, `toArray()`, default values, and edge cases

### Test Placement Rules

- Type classes (`src/Types/**`) → `tests/Unit/Types/**Test.php`
- Enum classes (`src/Enums/**`) → `tests/Unit/Enums/**Test.php`
- RPC classes (`src/Rpc/**`) → `tests/Unit/Rpc/**Test.php`
- Support classes (`src/Support/**`) → `tests/Unit/Support/**Test.php`
- Transport classes (`src/Transport/**`) → `tests/Unit/Transport/**Test.php`
- JsonRpc classes (`src/JsonRpc/**`) → `tests/Unit/JsonRpc/**Test.php`
- Process classes (`src/Process/**`) → `tests/Unit/Process/**Test.php`
- Events (`src/Events/**`) → `tests/Unit/Events/**Test.php`

## Step 4: Write Tests

Write tests following the patterns observed in Step 3.

### Guidelines

- Use Pest `describe()`/`it()` syntax consistently.
- Use `expect()` for all assertions — never use PHPUnit-style `$this->assert*()`.
- Test both the "happy path" and edge cases (null values, empty arrays, missing keys).
- For `readonly class` types with `fromArray()`/`toArray()`:
  - Test creation with all fields populated
  - Test creation with minimal/default values
  - Test `toArray()` roundtrip
  - Test handling of optional/nullable fields
- For enum classes:
  - Test all cases exist
  - Test `from()` and `tryFrom()` behavior
- Keep tests focused and independent — each `it()` block should test one behavior.
- Do not add unnecessary comments. Only comment complex test logic.

### Example Test Structure

```php
<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SomeType;

describe('SomeType', function () {
    it('can be created from array with all fields', function () {
        $data = SomeType::fromArray([
            'field1' => 'value1',
            'field2' => true,
        ]);

        expect($data->field1)->toBe('value1')
            ->and($data->field2)->toBeTrue();
    });

    it('handles default values', function () {
        $data = SomeType::fromArray([]);

        expect($data->field1)->toBeNull()
            ->and($data->field2)->toBeFalse();
    });

    it('converts to array', function () {
        $data = SomeType::fromArray([
            'field1' => 'value1',
        ]);

        $array = $data->toArray();

        expect($array)->toHaveKey('field1', 'value1');
    });
});
```

## Step 5: Validate

1. Run the full test suite to ensure nothing is broken:
   ```bash
   composer run test
   ```
2. If any tests fail, fix them before proceeding.
3. Run the code style fixer on changed files:
   ```bash
   vendor/bin/pint --dirty
   ```

## Step 6: Save State to Cache Memory

Write the updated state to cache-memory with key `test-improver-state`:

```json
{
  "last_sdk_sync_pr": <number or null>,
  "last_run_at": "<ISO 8601 timestamp>",
  "improved_files": ["<list of src/ files that have been improved>"],
  "summary": "<brief description of what was done>"
}
```

Merge the new `improved_files` entries with the existing list from cache-memory — do not overwrite previous entries.

## Step 7: Create Pull Request

Create a draft PR with:
- **Title**: Concise description (e.g., "Add tests for SessionListFilter type class")
- **Body**: Include:
  - What tests were added and why
  - Coverage change summary (before/after for the targeted file, if available)
  - Whether this was triggered by Priority 1 (sdk-sync gap) or Priority 2 (coverage improvement)

## Important Notes

- This is a Laravel **package** project using Orchestra Testbench, not a standard Laravel app.
- The test base class is `Tests\TestCase` (defined in `tests/TestCase.php`).
- All test files must include `declare(strict_types=1);` at the top.
- Pest configuration is in `tests/Pest.php` — it extends `TestCase` for Feature, Unit, and E2E suites.
- Do not modify existing tests unless they are broken by your changes.
- Do not add new dependencies — use only what is already in `composer.json`.