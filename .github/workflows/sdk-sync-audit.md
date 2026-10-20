---
name: SDK Sync Audit
description: Find actionable SDK sync omissions and open separate, deduplicated implementation tasks without changing the SDK.
intent: Keep significant, feasible SDK compatibility gaps visible as cohesive implementation tasks without duplicating existing work.

on:
    schedule: weekly on saturday
    workflow_dispatch:
    report-blocked-version: false

timeout-minutes: 30

engine:
    id: copilot
    copilot-sdk: true
    model-routing:
        goal: cost
        mode: auto
        allowed-models:
            #- gpt-6.1-sol
            - gpt-6-luna
            #- claude-sonnet-5.5
            - claude-haiku-5.5

skills:
    - .github/skills/sdk-sync-issue-creation

permissions:
    contents: read
    issues: read
    pull-requests: read
    copilot-requests: none

checkout:
    - path: .
      submodules: recursive
      fetch-depth: 0

steps:
    - name: Update official SDK snapshot
      run: |
          set -euo pipefail
          mkdir -p /tmp/gh-aw/sdk-sync-audit
          git -C copilot-sdk rev-parse HEAD > /tmp/gh-aw/sdk-sync-audit/pinned-commit.txt
          git submodule update --remote --merge
          git -C copilot-sdk rev-parse HEAD > /tmp/gh-aw/sdk-sync-audit/upstream-commit.txt
          git rev-parse HEAD > /tmp/gh-aw/sdk-sync-audit/laravel-commit.txt

    - name: Set up PHP
      uses: shivammathur/setup-php@2.40.0
      with:
          php-version: 8.5
          extensions: tokenizer
          coverage: none

    - name: Detect structural SDK sync gaps
      run: |
          set -euo pipefail
          php .github/scripts/check-sdk-sync.php > /tmp/gh-aw/sdk-sync-audit/candidates.json
          jq '{
            missing_rpc_type_count: (.missing_rpc_types | length),
            missing_enum_count: (.missing_enums | length),
            rpc_methods, session_config, resume_session_config, client_options, hooks
          }' /tmp/gh-aw/sdk-sync-audit/candidates.json > /tmp/gh-aw/sdk-sync-audit/summary.json

    - name: Save existing issues and in-flight sync PRs
      env:
          GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          REPOSITORY: ${{ github.repository }}
      run: |
          set -euo pipefail
          gh api --paginate --slurp "repos/$REPOSITORY/issues?state=all&per_page=100" \
            | jq '[.[][] | select(has("pull_request") | not) |
                {number, title, body, state, state_reason, html_url, labels: [.labels[].name]}]' \
            > /tmp/gh-aw/sdk-sync-audit/existing-issues.json
          gh pr list --repo "$REPOSITORY" --state open --label sdk-sync --limit 100 \
            --json number,title,body,url,headRefName \
            > /tmp/gh-aw/sdk-sync-audit/open-sync-prs.json

tools:
    github:
        mode: gh-proxy
        toolsets: [repos, issues, pull_requests]
    edit: false

safe-outputs:
    activation-comments: false
    report-failure-as-issue: false
    report-incomplete:
        create-issue: false
    threat-detection:
        report-as-issue: false
    create-issue:
        title-prefix: "[SDK sync gap] "
        labels: [sdk-sync, copilot]
        max: 5
        deduplicate-by-title: true

network:
    allowed:
        - defaults
        - github
---

# SDK Sync Audit

This is an **audit only**. Read and apply the SDK Sync Issue Creation skill before assessing candidates. Create up to five separate issues for verified, novel, feasible implementation gaps; do not implement changes, edit files, commit, update the submodule again, or create PRs, comments, or an aggregate report issue.

## Prepared evidence

Read `/tmp/gh-aw/sdk-sync-audit/summary.json`, then selectively query relevant entries from `/tmp/gh-aw/sdk-sync-audit/candidates.json` with `jq`. Do not load the full RPC type inventory. Use `pinned-commit.txt`, `upstream-commit.txt`, and `laravel-commit.txt` to identify the snapshots compared; this is a current-state audit, not only changes since the pinned commit.

The candidate report detects structural differences in RPC type names, enum names, public RPC method names, configuration options, and hooks. It checks file existence rather than fields inside existing RPC types. Treat candidates as leads, not proof: verify each against the relevant upstream source and complete Laravel entry point before creating an issue.

`existing-issues.json` contains paginated open and closed issues; `open-sync-prs.json` contains in-flight `sdk-sync` PRs. If any required evidence is missing, invalid, or unreadable, call `report_incomplete`. If there are no verified, novel, feasible gaps, call `noop`. Publish issues only through `create-issue`.
