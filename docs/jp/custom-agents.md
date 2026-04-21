# Custom Agents

カスタムエージェントを定義すると、用途ごとに役割を分けたサブエージェント構成を作れます。  
Laravel版では `SessionConfig` の `customAgents` を使って設定します。

## 基本的な使い方

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;

Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(prompt: '認証周りの設計を調査して改善案を提案して');

    dump($response->content());
}, config: new SessionConfig(
    customAgents: [
        [
            'name' => 'researcher',
            'displayName' => 'Research Agent',
            'description' => 'コードを調査して要点を整理する',
            'tools' => ['grep', 'glob', 'view'],
            'prompt' => 'You are a research assistant. Analyze code and summarize findings.',
        ],
        [
            'name' => 'editor',
            'displayName' => 'Editor Agent',
            'description' => '必要最小限のコード変更を行う',
            'tools' => ['view', 'edit', 'bash'],
            'prompt' => 'You are a code editor. Make minimal, surgical changes only.',
        ],
    ],
));
```

配列形式でも同様に指定できます。

```php
Copilot::run(
    prompt: 'READMEを改善して',
    config: [
        'customAgents' => [
            [
                'name' => 'docs-writer',
                'description' => 'ドキュメント執筆を担当する',
                'prompt' => 'Write clear technical documentation.',
            ],
        ],
    ],
);
```

## 設定項目

| プロパティ | 型 | 必須 | 説明 |
|---|---|---|---|
| `name` | `string` | ✅ | エージェントの識別子 |
| `displayName` | `string` |  | 表示名 |
| `description` | `string` |  | ランタイムが委譲先を選ぶ際の説明 |
| `tools` | `?array` |  | 使用可能ツール。`null` / 省略時はすべて利用可能 |
| `prompt` | `string` | ✅ | エージェント専用のシステムプロンプト |
| `mcpServers` | `array` |  | このエージェント専用のMCPサーバー設定 |
| `infer` | `bool` |  | 自動選択対象にするか（デフォルト: `true`） |
| `skills` | `array` |  | 起動時に読み込むスキル名のリスト |

> `description` は具体的に書くほど、自動選択の精度が上がります。

## セッション開始時に特定エージェントを選ぶ

`agent` を指定すると、最初のターンから特定のカスタムエージェントを有効化できます。

```php
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    customAgents: [
        [
            'name' => 'researcher',
            'prompt' => 'You analyze code and answer questions.',
        ],
        [
            'name' => 'editor',
            'prompt' => 'You edit code with minimal changes.',
        ],
    ],
    agent: 'researcher',
);
```

## 自動選択を無効にする（`infer: false`）

危険度の高い操作を行うエージェントは、自動選択を無効にして明示的に使う運用が安全です。

```php
$config = new SessionConfig(
    customAgents: [
        [
            'name' => 'dangerous-cleanup',
            'description' => '不要ファイルを削除する',
            'tools' => ['bash', 'edit', 'view'],
            'prompt' => 'Remove dead code and unused files carefully.',
            'infer' => false,
        ],
    ],
);
```

## デフォルトエージェントからツールを隠す

`defaultAgent.excludedTools` を使うと、メインエージェントだけから特定ツールを隠せます。  
そのツールを使えるカスタムエージェントへ委譲しやすくなります。

```php
$config = new SessionConfig(
    tools: [...],
    defaultAgent: [
        'excludedTools' => ['analyze-codebase'],
    ],
    customAgents: [
        [
            'name' => 'researcher',
            'tools' => ['analyze-codebase'],
            'prompt' => 'You perform deep codebase analysis.',
        ],
    ],
);
```

## サブエージェントイベントを監視する

サブエージェント実行中は `subagent.*` イベントが流れます。  
`onEvent` または `$session->on()` で監視できます。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionEvent;

Copilot::start(function (CopilotSession $session) {
    $session->on(function (SessionEvent $event): void {
        if ($event->is(SessionEventType::SUBAGENT_STARTED)) {
            info('Sub-agent started', $event->toArray());
        }

        if ($event->is(SessionEventType::SUBAGENT_COMPLETED)) {
            info('Sub-agent completed', $event->toArray());
        }

        if ($event->is(SessionEventType::SUBAGENT_FAILED)) {
            info('Sub-agent failed', $event->toArray());
        }
    });

    $session->sendAndWait(prompt: 'このリポジトリの認証実装を調べて');
});
```

## ベストプラクティス

- `researcher`（読み取り中心）と `editor`（変更担当）を分ける
- `tools` は必要最小限にし、権限を絞る
- `description` を具体的に書いて自動委譲精度を上げる
- `subagent.failed` を拾ってリトライやフォールバック戦略を用意する

## 参考

- [SessionConfig](./session-config.md)
- [SessionEvent](./session-event.md)
- [MCP](./mcp.md)
- [Tools](./tools.md)
