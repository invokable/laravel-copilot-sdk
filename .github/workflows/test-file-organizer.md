---
name: Test File Organizer
description: Review and improve test file names and organization across the tests directory.
intent: Make the test suite easier to navigate by aligning test file names and descriptions with the behavior they cover.

on:
  schedule: weekly on saturday around 6:00 utc+9 # 日本時間で日曜午前6時頃。
  workflow_dispatch:
  skip-if-match: 'is:pr is:open "gh-aw-workflow-id: test-file-organizer" in:body'

steps:
  - name: Set up PHP
    uses: shivammathur/setup-php@2.37.2
    with:
      php-version: 8.5
      extensions: mbstring, xml, phar, dom, tokenizer
      coverage: xdebug
  - name: Install Composer dependencies
    run: composer install -q --no-interaction --prefer-dist --optimize-autoloader

permissions:
  contents: read
  models: read
  pull-requests: read

model: gpt-6-luna
engine:
  id: copilot
checkout:
  - path: .
    submodules: recursive

tools:
  github:
    mode: gh-proxy
    toolsets: [repos, pull_requests]

safe-outputs:
  create-pull-request:
    reviewers: [kawax]
    draft: true
    if-no-changes: ignore
    signed-commits: false
    allowed-files:
      - "tests/**/*.php"

network:
  allowed:
    - defaults
    - github
    - php
    - threat-detection
---

# Test File Organizer

Review the complete `tests/` tree and make a focused pass to ensure test filenames and suite descriptions clearly identify the behavior they cover. This workflow is separate from `test-improver`: do not add coverage or otherwise improve test behavior.

## Review and organize

1. Read repository instructions that apply to `tests/`, then inspect the test configuration and all test files under `tests/`. Do not limit the review to RPC tests or files matching a particular naming pattern.
2. Identify test files whose names are opaque, generated-looking, overly generic, or inconsistent with the subject they actually test. For example, replace version-only names such as `NewV102RpcTest.php` with names based on the tested RPC, type, or behavior.
3. Use the contents of each file and the related `src/` classes to establish its subject before renaming or moving it. Follow existing test directory and naming conventions. Prefer:
   - renaming a file when its tests cover one coherent subject;
   - moving tests into an existing file only when they test the same subject and the resulting file remains easy to understand;
   - splitting a file into multiple clearly named files when it contains distinct subjects or independently understandable groups.
4. Update only `describe()` labels that are vague or misleading, such as `describe('new upstream session RPC groups', ...)`. Replace them with concise descriptions of the actual class or behavior under test. Preserve the suite structure otherwise.
5. Do not modify test logic: preserve every test case, assertion, setup, mock, input, and expected value exactly. Do not add or remove tests, change production code, or edit files outside `tests/`. The only content changes permitted are clearer `describe()` labels; file moves, renames, and splitting complete existing test blocks are permitted.

## Validate and submit

1. Run `composer run test` before making changes. If the baseline suite fails, do not reorganize files; call `noop` with a short reason.
2. Make only the naming and organization changes described above, then run `composer run test` again. Confirm the suite passes and that the number of tests is unchanged.
3. Review the final diff to verify that it changes only test file paths and unclear `describe()` labels, with no additions, deletions, or edits to test behavior.
4. If the suite fails after a move or split, correct only a path-dependent issue caused by that move and rerun the suite. If preserving the passing suite would require changing test behavior or unrelated files, do not submit the changes; call `noop` with a short reason.
5. If no clearly beneficial naming or organization changes are needed, call `noop` with a short reason.
6. Otherwise, create one draft pull request using the configured safe output. Summarize the naming/organization changes and report the before-and-after test results and unchanged test count.
