---
name: Test Improver
description: Weekly workflow to improve test coverage incrementally by adding missing tests.

on:
  schedule: weekly on saturday around 4:00 utc+9 # 日本時間で日曜午前4時頃。
  workflow_dispatch:
  skip-if-match: 'is:pr is:open "gh-aw-workflow-id: test-improver" in:body'

steps:
    -   name: Set up PHP
        uses: shivammathur/setup-php@2.40.0
        with:
            php-version: 8.5
            extensions: mbstring, xml, phar, dom, tokenizer, iconv
            coverage: xdebug
            ini-values: memory_limit=512M
    -   name: Install Composer dependencies
        run: composer install -q --no-interaction --prefer-dist --optimize-autoloader
    -   name: Run coverage
        run: |
            mkdir -p /tmp/gh-aw
            vendor/bin/pest --compact --coverage --colors=never > /tmp/gh-aw/coverage.txt 2>&1 || true
            #cat /tmp/gh-aw/coverage.txt
            #exit 1

permissions:
  contents: read
  pull-requests: read
  issues: read
  copilot-requests: none

engine:
  id: copilot
  copilot-sdk: true
  model: gpt-6-luna

checkout:
  - path: .
    submodules: recursive

tools:
  github:
    mode: gh-proxy
    toolsets: [default]
  cli-proxy: true
  bash: ["*"]
  edit: true

safe-outputs:
  create-pull-request:
    labels: [test-improver, copilot]
    reviewers: [kawax]
    draft: true
    expires: 14d
    fallback-as-issue: true
    if-no-changes: ignore
    signed-commits: false

network:
  allowed:
    - defaults
    - github
    - threat-detection
    - php
---

# Test Improver

You are responsible for incrementally improving test coverage in this Laravel Copilot SDK package.
This workflow runs weekly. Make small, focused changes — typically one source file's tests per run.

## Step 1: Coverage-Based Selection

Coverage has already been measured by a prior step (`vendor/bin/pest --compact --coverage`). Do not re-run it.

1. Read `/tmp/gh-aw/coverage.txt`.
2. Identify files with the lowest coverage percentages.
3. Select **one** source file to improve, preferring the lowest coverage that is not in the skip list below.

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

## Step 2: Study Existing Test Patterns

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

## Step 3: Write Tests

Write tests following the patterns observed in Step 2.

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

## Step 4: Validate

1. Run the modified tests to ensure nothing is broken:
   ```bash
   vendor/bin/pest --dirty
   ```
2. If any tests fail, fix them before proceeding.
3. Run the code style fixer on changed files:
   ```bash
   vendor/bin/pint --dirty
   ```

If a PHP extension is missing, run the command with `-d extension=` added.
If you don't have enough memory, run it with `-d memory_limit=512M`.

## Step 5: Create Pull Request

Create a draft PR with:
- **Title**: Concise description (e.g., "Add tests for SessionListFilter type class")
- **Body**: Include:
  - What tests were added and why
  - Coverage change summary (before/after for the targeted file, if available)

## Important Notes

- This is a Laravel **package** project using Orchestra Testbench, not a standard Laravel app.
- The test base class is `Tests\TestCase` (defined in `tests/TestCase.php`).
- All test files must include `declare(strict_types=1);` at the top.
- Pest configuration is in `tests/Pest.php` — it extends `TestCase` for Feature, Unit, and E2E suites.
- Do not modify existing tests unless they are broken by your changes.
- Do not add new dependencies — use only what is already in `composer.json`.
