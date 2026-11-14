---
name: sdk-sync-docs
description: "Use when adding or updating Japanese RPC documentation under docs/jp/ for the Laravel Copilot SDK. Covers the split between the rpc.md index and the feature pages under docs/jp/rpc/, page placement, scope labeling, and index maintenance so any coding agent documents new RPC capabilities in the right place."
---

# SDK Sync Docs

Use this skill whenever a change adds or updates RPC documentation in `docs/jp/`. The RPC docs are split by feature; placing examples in the wrong file makes them hard to find and review.

## Structure

- `references/docs-list.md`
- `docs/jp/rpc.md` is an index and overview only. It holds the ServerRpc and SessionRpc method lists, the basic calling example, the array-arguments note, and the Testing note. Do not add usage examples to `rpc.md`.
- Usage examples live in feature pages under `docs/jp/rpc/`. Each page covers one feature area for one scope. Organize by feature, the way the official SDK docs do; do not organize pages around RPC plumbing.
- Every feature page starts with this header, with the scope line naming its RPC class:

  ```markdown
  # <Feature title>

  対象: ServerRpc（Client に紐づくサーバー全体の操作）

  RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。
  ```

  For session pages the scope line is `対象: SessionRpc（Session に紐づくセッション単位の操作）`.

## Choose the target page

Start from the index in `rpc.md` (the two `使用例（機能別ページ）` lists) and pick the page whose feature and scope match the change. Never mix scopes in one page: a feature that exists in both scopes has separate pages (for example `server-mcp.md` and `session-mcp.md`).

ServerRpc pages (`Copilot::client()->rpc()`):

| Page | Covers |
|---|---|
| `server-models.md` | models, tools, account |
| `server-local-models.md` | Azure ML local model management |
| `server-mcp.md` | MCP server config, registry search, catalog install/uninstall, discovery |
| `server-settings.md` | user settings, contentExclusion, managedSettings, settings files |
| `server-inference.md` | llmInference HTTP hooks, sessionFs provider registration, extension launch resolution |
| `server-sessions.md` | persisted sessions, host support, environments, runtime/host lifecycle |
| `server-skills-plugins.md` | skills, catalog, plugins, agents, instructions, extensions |

SessionRpc pages (`$session->rpc()`):

| Page | Covers |
|---|---|
| `session-model.md` | model, mode, name, plan |
| `session-workspace.md` | workspaces, instructions, LSP, fleet, agent, session skills |
| `session-mcp.md` | in-session MCP servers, MCP Apps |
| `session-plugins.md` | plugins, extensions |
| `session-settings.md` | managedSettings, debug, contentExclusion, history/compaction |
| `session-tools.md` | tools, permissions, commands, UI elicitation |
| `session-activity.md` | log, shell, usage, telemetry, auth |
| `session-provider.md` | provider endpoint/status, BYOK registry updates and withdraw |
| `session-queue-tasks.md` | queue, schedule, background tasks |
| `session-remote.md` | remote sessions, sandbox |
| `session-info.md` | session metadata, local session management |
| `session-canvas.md` | session canvas |
| `session-workflow.md` | feature flags, workflow |
| `session-fs.md` | SessionFS callback handlers |

## Update rules

- Add examples to the matching feature page, next to the examples for the same RPC group. Examples are PHP code blocks whose groups are introduced by a `// <feature>` comment, following the page's existing style; prose sections use `##` headings.
- When a change adds a new RPC method, also add it to the method list of the matching group in `rpc.md`'s ServerRpc or SessionRpc overview. The overview stays in `rpc.md`; the example goes to the feature page.
- If no existing page fits, create `docs/jp/rpc/server-<feature>.md` or `docs/jp/rpc/session-<feature>.md` with the standard header above, and add it to the matching index list in `rpc.md` in the same change. A new page without an index entry is incomplete.
- Write docs in Japanese, preserving exact API, class, and path identifiers. Match the terminology and level of detail of neighboring examples instead of restating the implementation.
- When moving or renaming a documented section, update inbound links from other docs (for example `docs/jp/mcp.md` links to `rpc/session-mcp.md#mcp-apps-experimental`).
- Non-RPC docs keep their existing homes: event types in `session-event.md`, and other behavior in its existing relevant guide under `docs/jp/`.
