---
name: SDK Sync Audit
description: Find actionable SDK sync omissions and open separate, deduplicated implementation tasks without changing the SDK.
intent: Help maintainers track meaningful SDK compatibility gaps and implementation opportunities without duplicating known work.

on:
    schedule: weekly on saturday
    workflow_dispatch:
    report-blocked-version: false

max-ai-credits: 500
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
    discussions: read
    copilot-requests: none

checkout:
    - path: .
      submodules: recursive
      fetch-depth: 0

steps:
    - name: Set up PHP
      uses: shivammathur/setup-php@2.40.0
      with:
          php-version: 8.5
          extensions: tokenizer
          coverage: none

    - name: Detect structural SDK sync gaps
      run: |
          set -euo pipefail
          mkdir -p /tmp/gh-aw/sdk-sync-audit
          php .github/scripts/check-sdk-sync.php > /tmp/gh-aw/sdk-sync-audit/candidates.json
          jq '{
            missing_rpc_type_count: (.missing_rpc_types | length),
            missing_rpc_api_count: (.missing_rpc_apis | length),
            missing_enum_count: (.missing_enums | length),
            rpc_method_exclusions,
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
        toolsets: [repos, issues, pull_requests, discussions]
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
    create-discussion:
        category: "copilot"
        title-prefix: "[SDK Sync Audit] "
        max: 1
        expires: false
        fallback-to-issue: false

network:
    allowed:
        - defaults
        - github
---

# SDK Sync Audit

This is an **audit only**. Read and apply the SDK Sync Issue Creation skill before assessing candidates. Create up to five separate issues for verified, novel, feasible implementation gaps, and publish one audit report discussion; do not implement changes, edit files, commit, update the submodule, or create PRs or comments.

## Previous audit report

At the beginning of the audit, before reading the prepared evidence or examining code, call `list_discussion_categories` and find the `copilot` category. Use its ID with `list_discussions`, paging as needed, to find the most recent discussion whose title starts with `[SDK Sync Audit] `, then read its full body with `get_discussion`. If there is no matching discussion (including on the first run), continue without one. Treat the previous report as historical context, not proof of current implementation status; compare it with current findings only where the normal audit already provides evidence.

## Prepared evidence

Read `/tmp/gh-aw/sdk-sync-audit/summary.json`, then selectively query relevant entries from `/tmp/gh-aw/sdk-sync-audit/candidates.json` with `jq`. Do not load the full RPC type inventory. This is a current-state audit of the checked-out official SDK submodule against the Laravel implementation; do not update the submodule as part of the audit.

The candidate report detects structural differences in RPC type names, Java RPC API group files mapped to Laravel `Pending*` files, enum names, RPC method names, configuration options, and hooks. Query `.missing_rpc_apis[]` separately from `.missing_rpc_types[]`; Java `*Api.java` files are not RPC data types. `rpc_method_exclusions` lists upstream methods intentionally omitted from parity candidates, with reasons. It checks file existence rather than fields inside existing RPC types. Treat candidates as leads, not proof: verify each against the relevant upstream source and complete Laravel entry point before creating an issue.

`existing-issues.json` contains paginated open and closed issues; `open-sync-prs.json` contains in-flight `sdk-sync` PRs. If any required evidence is missing, invalid, or unreadable, call `report_incomplete`. If there are no verified, novel, feasible gaps, do not create an issue. Publish issues only through `create-issue`.

After completing the audit, publish exactly one discussion with `create-discussion`, even when no new implementation issue is warranted. Use the configured title prefix and report:

- **Observed implementation status:** summarize what this audit revealed about the project's implementation, including supported areas, verified gaps, and relevant in-flight work. State that this is a non-exhaustive snapshot based only on evidence encountered during the normal audit. Do not inspect additional code or documentation solely to prepare this section.
- **Candidates for a future audit:** list implementation features or areas worth reconsidering, based only on findings already encountered. These are tentative candidates, not confirmed gaps or commitments; the next audit must re-check them because implementation may have changed in the meantime. Do not repeat a candidate as an unresolved gap if current evidence shows it is already implemented.
- **Audit outcome:** summarize verified gaps for which issue creation was requested and note when no new issue was needed. Do not claim an issue was successfully created unless that is confirmed.

Keep the report concise and distinguish confirmed observations from tentative candidates. Call `noop` only when a report cannot be produced and `report_incomplete` is not usable; absence of new verified gaps alone is not a reason to skip the discussion.
