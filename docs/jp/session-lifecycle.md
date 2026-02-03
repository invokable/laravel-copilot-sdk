# セッションライフサイクルイベント

セッションライフサイクルイベントは、セッションの作成、削除、更新、およびフォアグラウンド/バックグラウンドの状態変更（TUI+サーバーモード時）を通知するイベントです。

## イベントタイプ

`SessionLifecycleEventType` enumで定義されている5つのイベントタイプがあります：

| イベント | 説明 |
|---------|------|
| `session.created` | 新しいセッションが作成された |
| `session.deleted` | セッションが削除された |
| `session.updated` | セッションが更新された |
| `session.foreground` | セッションがフォアグラウンドに移動した |
| `session.background` | セッションがバックグラウンドに移動した |

```php
use Revolution\Copilot\Enums\SessionLifecycleEventType;

// イベントタイプの値を取得
echo SessionLifecycleEventType::SESSION_CREATED->value; // 'session.created'
```

## イベントの購読

`onLifecycle()` メソッドを使用してセッションライフサイクルイベントを購読できます：

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionLifecycleEvent;

// Clientを取得してライフサイクルイベントを購読
$client = Copilot::client();

// 全てのライフサイクルイベントを購読
$unsubscribe = $client->onLifecycle(function (SessionLifecycleEvent $event) {
    match ($event->type) {
        SessionLifecycleEventType::SESSION_CREATED => info("セッション作成: {$event->sessionId}"),
        SessionLifecycleEventType::SESSION_DELETED => info("セッション削除: {$event->sessionId}"),
        SessionLifecycleEventType::SESSION_FOREGROUND => info("フォアグラウンド: {$event->sessionId}"),
        SessionLifecycleEventType::SESSION_BACKGROUND => info("バックグラウンド: {$event->sessionId}"),
        default => null,
    };
});

// 購読を解除
$unsubscribe();
```

## SessionLifecycleEvent

`SessionLifecycleEvent` はライフサイクルイベントの詳細を含むreadonly classです：

```php
use Revolution\Copilot\Types\SessionLifecycleEvent;
use Revolution\Copilot\Types\SessionLifecycleEventMetadata;

readonly class SessionLifecycleEvent implements Arrayable
{
    public function __construct(
        public SessionLifecycleEventType $type,  // イベントタイプ
        public string $sessionId,                 // セッションID
        public ?SessionLifecycleEventMetadata $metadata = null,  // メタデータ（削除時は含まれない）
    ) {}
}
```

### SessionLifecycleEventMetadata

セッションのメタデータを含むクラスです（削除イベントには含まれません）：

```php
readonly class SessionLifecycleEventMetadata implements Arrayable
{
    public function __construct(
        public string $startTime,      // セッション開始時刻
        public string $modifiedTime,   // 最終更新時刻
        public ?string $summary = null, // セッションの要約
    ) {}
}
```

## フォアグラウンドセッション管理

TUI+サーバーモード（`--ui-server`）で動作しているサーバーに接続している場合、フォアグラウンドセッションを管理できます。

### 現在のフォアグラウンドセッションを取得

```php
use Revolution\Copilot\Facades\Copilot;

$client = Copilot::client();

// TUIに表示されている現在のセッションIDを取得
$sessionId = $client->getForegroundSessionId();

if ($sessionId !== null) {
    echo "現在のフォアグラウンドセッション: {$sessionId}";
}
```

### フォアグラウンドセッションを設定

```php
use Revolution\Copilot\Facades\Copilot;

$client = Copilot::client();

// TUIに特定のセッションを表示
$client->setForegroundSessionId('session-123');
```

## ForegroundSessionInfo

フォアグラウンドセッションの情報を含むreadonly classです：

```php
use Revolution\Copilot\Types\ForegroundSessionInfo;

readonly class ForegroundSessionInfo implements Arrayable
{
    public function __construct(
        public ?string $sessionId = null,      // フォアグラウンドセッションID
        public ?string $workspacePath = null,  // ワークスペースパス
    ) {}
}
```

## TCPモードでの使用

TUI+サーバーモードでCopilot CLIを起動し、SDKから接続することでライフサイクルイベントを活用できます：

```bash
# TUI+サーバーモードでCopilot CLIを起動
copilot --ui-server --port 8080
```

```php
use Revolution\Copilot\Facades\Copilot;

// TCPモードでサーバーに接続
$client = Copilot::useTcp('tcp://127.0.0.1:8080')->client();

// ライフサイクルイベントを購読
$client->onLifecycle(function (SessionLifecycleEvent $event) {
    // イベント処理
});
```

## 注意事項

- `session.foreground` と `session.background` イベントは、TUI+サーバーモードでのみ発生します
- セッション削除時（`session.deleted`）には `metadata` プロパティは `null` になります
- `onLifecycle()` は購読解除用のコールバック関数を返します
