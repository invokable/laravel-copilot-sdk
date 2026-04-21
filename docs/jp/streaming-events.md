# Streaming Session Events

`SessionConfig`で`streaming: true`を有効にすると、セッション中の動作がイベントとして順次配信されます。
このページは、公式の`streaming-events.md`をLaravel向けに読みやすくした日本語版です。

> `docs/jp/session-event.md`はLaravel向けに拡張した`SessionEvent`クラス自体の説明です。  
> このページは「どのイベントタイプで、どのデータが届くか」のリファレンスです。

## 概要

Copilotエージェントの処理（推論、メッセージ生成、ツール実行、権限確認など）はすべてセッションイベントとして流れます。

- **Ephemeral event**: リアルタイム配信のみ。セッションログには永続化されない（再開時に再生されない）
- **Persisted event**: セッションログに保存される（再開時に再生される）
- **Delta event**: 断片的に届く差分イベント（`deltaContent`など）。連結して全文を構成する
- **`parentId` chain**: 各イベントが直前イベントIDを参照する連鎖

## Event Envelope（共通フィールド）

すべてのイベントは以下の共通構造を持ちます。

| Field | Type | 説明 |
|---|---|---|
| `id` | `string` | イベント固有ID（UUID v4） |
| `timestamp` | `string` | 作成時刻（ISO 8601） |
| `parentId` | `string \| null` | 直前イベントID（先頭イベントは`null`） |
| `ephemeral` | `boolean?` | 一時イベントの場合`true` |
| `type` | `string` | イベントタイプ |
| `data` | `object` | イベント固有のペイロード |

## Laravelでの購読例

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;

Copilot::start(function (CopilotSession $session): void {
    // 全イベント
    $session->on(function (SessionEvent $event): void {
        info($event->type(), $event->toArray());
    });

    // 特定イベント
    $session->on(SessionEventType::ASSISTANT_MESSAGE_DELTA, function (SessionEvent $event): void {
        echo $event->deltaContent();
    });

    $session->sendAndWait(prompt: 'Laravelの特徴を教えて');
}, config: new SessionConfig(streaming: true));
```

## 主なイベントカテゴリ

## Assistant Events

### `assistant.turn_start`
ターン開始。

- `turnId`（必須）
- `interactionId`（任意）

### `assistant.intent`（ephemeral）
現在の実行意図（例: `Exploring codebase`）。

- `intent`（必須）

### `assistant.reasoning`
推論ブロックの完成版。

- `reasoningId`（必須）
- `content`（必須）

### `assistant.reasoning_delta`（ephemeral）
推論テキストの差分。

- `reasoningId`（必須）
- `deltaContent`（必須）

### `assistant.message`
アシスタントの完成メッセージ。

主なフィールド:

- `messageId`（必須）
- `content`（必須）
- `toolRequests`（任意）
- `reasoningOpaque` / `reasoningText` / `encryptedContent`（任意）
- `phase` / `outputTokens` / `interactionId`（任意）
- `parentToolCallId`（任意、サブエージェント由来時）

### `assistant.message_delta`（ephemeral）
メッセージ本文の差分。

- `messageId`（必須）
- `deltaContent`（必須）
- `parentToolCallId`（任意）

### `assistant.turn_end`
ターン終了。

- `turnId`（必須）

### `assistant.usage`（ephemeral）
API呼び出し単位の使用量情報。

主なフィールド:

- `model`（必須）
- `inputTokens` / `outputTokens` / `cost` / `duration`（任意）
- `apiCallId` / `providerCallId`（任意）
- `quotaSnapshots` / `copilotUsage`（任意）

### `assistant.streaming_delta`（ephemeral）
低レベルな受信進捗。

- `totalResponseSizeBytes`（必須）

## Tool Execution Events

### `tool.execution_start`
ツール実行開始。

- `toolCallId`（必須）
- `toolName`（必須）
- `arguments` / `mcpServerName` / `mcpToolName` / `parentToolCallId`（任意）

### `tool.execution_partial_result`（ephemeral）
ツール実行中の部分出力。

- `toolCallId`（必須）
- `partialOutput`（必須）

### `tool.execution_progress`（ephemeral）
進捗メッセージ。

- `toolCallId`（必須）
- `progressMessage`（必須）

### `tool.execution_complete`
ツール実行完了（成功/失敗）。

- `toolCallId`（必須）
- `success`（必須）
- `result`（成功時）
- `error`（失敗時）
- `toolTelemetry` / `parentToolCallId`（任意）

### `tool.user_requested`
ユーザー明示要求でのツール呼び出し。

- `toolCallId`（必須）
- `toolName`（必須）
- `arguments`（任意）

## Session Lifecycle Events

### `session.idle`（ephemeral）
現在の処理が完了し、次の入力待ち。

- `backgroundTasks`（任意）

### `session.error`
セッション処理中のエラー。

- `errorType`（必須）
- `message`（必須）
- `stack` / `statusCode` / `providerCallId`（任意）

### `session.compaction_start`
コンテキスト圧縮開始（`data`は空オブジェクト）。

### `session.compaction_complete`
コンテキスト圧縮完了。

主なフィールド:

- `success`（必須）
- `error`（任意）
- `preCompactionTokens` / `postCompactionTokens`（任意）
- `summaryContent` / `checkpointPath`（任意）

### `session.title_changed`（ephemeral）
自動タイトル更新。

- `title`（必須）

### `session.context_changed`
作業コンテキスト変更。

- `cwd`（必須）
- `gitRoot` / `repository` / `branch`（任意）

### `session.usage_info`（ephemeral）
コンテキストウィンドウ利用状況。

- `tokenLimit`（必須）
- `currentTokens`（必須）
- `messagesLength`（必須）

### `session.task_complete`
タスク完了通知。

- `summary`（任意）

### `session.shutdown`
セッション終了。

主なフィールド:

- `shutdownType`（必須）
- `errorReason`（任意）
- `totalPremiumRequests` / `totalApiDurationMs`（必須）
- `codeChanges` / `modelMetrics`（必須）

## Permission / User Input Events

### `permission.requested`（ephemeral）
権限確認要求。

- `requestId`（必須）
- `permissionRequest`（必須）

`permissionRequest.kind`:

- `shell`
- `write`
- `read`
- `mcp`
- `url`
- `memory`
- `custom-tool`

### `permission.completed`（ephemeral）
権限確認の解決結果。

- `requestId`（必須）
- `result.kind`（必須）

### `user_input.requested`（ephemeral）
ユーザーへの質問。

- `requestId`（必須）
- `question`（必須）
- `choices` / `allowFreeform`（任意）

### `user_input.completed`（ephemeral）
ユーザー入力完了。

- `requestId`（必須）

### `elicitation.requested`（ephemeral）
構造化入力（フォーム）要求。

- `requestId`（必須）
- `message`（必須）
- `requestedSchema`（必須）

### `elicitation.completed`（ephemeral）
構造化入力の完了。

- `requestId`（必須）

## Sub-Agent / Skill Events

### `subagent.started`

- `toolCallId`（必須）
- `agentName` / `agentDisplayName` / `agentDescription`（必須）

### `subagent.completed`

- `toolCallId`（必須）
- `agentName` / `agentDisplayName`（必須）

### `subagent.failed`

- `toolCallId`（必須）
- `agentName` / `agentDisplayName`（必須）
- `error`（必須）

### `subagent.selected`

- `agentName`（必須）
- `agentDisplayName`（必須）
- `tools`（必須、`null`許容）

### `subagent.deselected`
デフォルトエージェントへ復帰（`data`は空オブジェクト）。

### `skill.invoked`

- `name` / `path` / `content`（必須）
- `allowedTools` / `pluginName` / `pluginVersion`（任意）

## Other Events

### `abort`

- `reason`（必須）

### `user.message`

- `content`（必須）
- `transformedContent` / `attachments` / `source` / `agentMode` / `interactionId`（任意）

### `system.message`

- `content`（必須）
- `role`（必須）
- `name` / `metadata`（任意）

### `external_tool.requested`（ephemeral）

- `requestId` / `sessionId` / `toolCallId` / `toolName`（必須）
- `arguments`（任意）

### `external_tool.completed`（ephemeral）

- `requestId`（必須）

### `exit_plan_mode.requested`（ephemeral）

- `requestId` / `summary` / `planContent` / `actions` / `recommendedAction`（必須）

### `exit_plan_mode.completed`（ephemeral）

- `requestId`（必須）

### `command.queued`（ephemeral）

- `requestId`（必須）
- `command`（必須）

### `command.completed`（ephemeral）

- `requestId`（必須）

## 典型的なイベント順序

```text
assistant.turn_start
├── assistant.intent (ephemeral)
├── assistant.reasoning_delta (ephemeral, 複数回)
├── assistant.reasoning
├── assistant.message_delta (ephemeral, 複数回)
├── assistant.message
├── assistant.usage (ephemeral)
├── [必要に応じて permission / tool.* がループ]
assistant.turn_end
session.idle (ephemeral)
```

## 全イベント一覧（クイックリファレンス）

| Event Type | Ephemeral | Category |
|---|---|---|
| `assistant.turn_start` |  | Assistant |
| `assistant.intent` | ✅ | Assistant |
| `assistant.reasoning` |  | Assistant |
| `assistant.reasoning_delta` | ✅ | Assistant |
| `assistant.streaming_delta` | ✅ | Assistant |
| `assistant.message` |  | Assistant |
| `assistant.message_delta` | ✅ | Assistant |
| `assistant.turn_end` |  | Assistant |
| `assistant.usage` | ✅ | Assistant |
| `tool.user_requested` |  | Tool |
| `tool.execution_start` |  | Tool |
| `tool.execution_partial_result` | ✅ | Tool |
| `tool.execution_progress` | ✅ | Tool |
| `tool.execution_complete` |  | Tool |
| `session.idle` | ✅ | Session |
| `session.error` |  | Session |
| `session.compaction_start` |  | Session |
| `session.compaction_complete` |  | Session |
| `session.title_changed` | ✅ | Session |
| `session.context_changed` |  | Session |
| `session.usage_info` | ✅ | Session |
| `session.task_complete` |  | Session |
| `session.shutdown` |  | Session |
| `permission.requested` | ✅ | Permission |
| `permission.completed` | ✅ | Permission |
| `user_input.requested` | ✅ | User Input |
| `user_input.completed` | ✅ | User Input |
| `elicitation.requested` | ✅ | User Input |
| `elicitation.completed` | ✅ | User Input |
| `subagent.started` |  | Sub-Agent |
| `subagent.completed` |  | Sub-Agent |
| `subagent.failed` |  | Sub-Agent |
| `subagent.selected` |  | Sub-Agent |
| `subagent.deselected` |  | Sub-Agent |
| `skill.invoked` |  | Skill |
| `abort` |  | Control |
| `user.message` |  | User |
| `system.message` |  | System |
| `external_tool.requested` | ✅ | External Tool |
| `external_tool.completed` | ✅ | External Tool |
| `command.queued` | ✅ | Command |
| `command.completed` | ✅ | Command |
| `exit_plan_mode.requested` | ✅ | Plan Mode |
| `exit_plan_mode.completed` | ✅ | Plan Mode |

## 関連ドキュメント

- 公式ドキュメント: https://github.com/github/copilot-sdk/blob/main/docs/features/streaming-events.md
- Laravel版のイベントクラス解説: [SessionEvent](./session-event.md)
- ストリーミングの実装パターン: [Streaming](./streaming.md)
