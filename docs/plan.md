# Laravel版 Copilot CLI SDK 実装計画

## 概要

GitHub Copilot CLI への JSON-RPC クライアントを Laravel/PHP で実装する。公式SDKの Node.js 版と Python 版を参考に、PHP でも同等の機能を実現する。

## 調査結果サマリー

### アーキテクチャ

```
Your Application
       ↓
  SDK Client (PHP)
       ↓ JSON-RPC (stdio)
  Copilot CLI (server mode)
```

- **通信方式**: stdio トランスポート（TCP も可能だが stdio がデフォルト）
- **プロトコル**: JSON-RPC 2.0 with Content-Length ヘッダー
- **プロトコルバージョン**: `1`（sdk-protocol-version.json）

### JSON-RPC メッセージフォーマット

リクエスト:
```
Content-Length: {length}\r\n\r\n{"jsonrpc":"2.0","id":"uuid","method":"method.name","params":{}}
```

レスポンス:
```
Content-Length: {length}\r\n\r\n{"jsonrpc":"2.0","id":"uuid","result":{}}
```

### 主要なJSON-RPCメソッド

| メソッド | パラメータ | 説明 |
|---------|-----------|------|
| `ping` | message? | 接続確認、protocolVersion を返す |
| `session.create` | model?, sessionId?, tools?, systemMessage?, availableTools?, excludedTools?, provider?, requestPermission?, streaming?, mcpServers?, customAgents? | セッション作成 |
| `session.resume` | sessionId, tools?, provider?, requestPermission?, streaming?, mcpServers?, customAgents? | セッション再開 |
| `session.send` | sessionId, prompt, attachments?, mode? | メッセージ送信 |
| `session.getMessages` | sessionId | メッセージ履歴取得 |
| `session.destroy` | sessionId | セッション破棄 |
| `session.abort` | sessionId | 処理中止 |
| `session.getLastId` | - | 最後のセッションID取得 |
| `session.delete` | sessionId | セッション削除 |
| `session.list` | - | セッション一覧 |

### サーバーからの通知/リクエスト

| 種別 | メソッド | 説明 |
|------|---------|------|
| notification | `session.event` | セッションイベント（assistant.message, session.idle, etc.） |
| request | `tool.call` | ツール呼び出しリクエスト |
| request | `permission.request` | 権限リクエスト |

### セッションイベントタイプ

- `session.start`, `session.resume`, `session.error`, `session.idle`, `session.info`
- `session.model_change`, `session.handoff`, `session.truncation`, `session.usage_info`
- `session.compaction_start`, `session.compaction_complete`
- `user.message`, `pending_messages.modified`
- `assistant.turn_start`, `assistant.intent`, `assistant.reasoning`, `assistant.reasoning_delta`
- `assistant.message`, `assistant.message_delta`, `assistant.turn_end`, `assistant.usage`
- `abort`
- `tool.user_requested`, `tool.execution_start`, `tool.execution_partial_result`, `tool.execution_complete`
- `subagent.started`, `subagent.completed`, `subagent.failed`, `subagent.selected`
- `hook.start`, `hook.end`
- `system.message`

## 実装方針

### 1. PHPでのプロセス管理

Laravel の `Illuminate\Support\Facades\Process` を使用してCLIサーバーを起動・管理。

```php
$process = Process::start('copilot --server --stdio');
```

### 2. JSON-RPC実装

Python版の `jsonrpc.py` を参考に、Content-Lengthヘッダー付きのJSON-RPC通信を実装。

- リクエスト/レスポンスの同期管理
- 通知ハンドラー
- リクエストハンドラー（tool.call, permission.request）

### 3. 同期 vs 非同期

PHPは基本的に同期処理。Node.js/Pythonのasync/awaitに相当する部分は：
- `sendAndWait()` ではイベントループ的にstdoutを読み続ける
- `session.idle` イベントを待って完了判定

---

## 実装タスク

### Phase 1: Core Infrastructure

- [x] **1.1 JSON-RPC Client** (`src/JsonRpc/JsonRpcClient.php`)
  - Content-Length 付き JSON-RPC 2.0 実装
  - リクエスト送信・レスポンス受信
  - 通知ハンドラー
  - リクエストハンドラー（サーバーからのリクエスト対応）

- [x] **1.2 Process Manager** (`src/Process/ProcessManager.php`)
  - `copilot --server --stdio` の起動
  - stdin/stdout パイプ管理
  - プロセス終了検知

### Phase 2: Client & Session

- [x] **2.1 CopilotClient** (`src/Client.php`, `src/Contracts/CopilotClient.php`)
  - サーバー起動/停止
  - プロトコルバージョン検証
  - セッション作成/再開/一覧/削除
  - ping

- [x] **2.2 CopilotSession** (`src/Session.php`, `src/Contracts/CopilotSession.php`)
  - send() / sendAndWait()
  - イベントハンドラー登録（on()）
  - イベントディスパッチ
  - destroy() / abort()
  - getMessages()

- [ ] **2.3 Types & Data Objects** (`src/Types/`)
  - CopilotClientOptions
  - SessionConfig
  - MessageOptions
  - SessionEvent（各イベントタイプ）
  - Tool, ToolInvocation, ToolResult
  - PermissionRequest, PermissionRequestResult

### Phase 3: Laravel Integration

- [x] **3.1 Facade** (`src/Facades/Copilot.php`)
  ```php
  Copilot::run($prompt);  // 簡易実行
  Copilot::start(fn($session) => ...);  // セッション使用
  ```

- [x] **3.2 Service Provider** (`src/CopilotSdkServiceProvider.php`)
  - クライアントのシングルトン登録
  - 設定ファイル公開

- [x] **3.3 Config** (`config/copilot.php`)
  - cli_path
  - log_level
  - timeout
  - auto_start/auto_restart

### ~~Phase 4: Artisan Commands~~

stdioでは意味がなさそうなのでスキップ。

### Phase 5: Testing Support

- [x] **5.1 Fake/Mock** (`src/Testing/CopilotFake.php`)
  ```php
  Copilot::fake();
  Copilot::fake(['*' => Copilot::response('4')]);
  ```

これはCopilot Facadeから使う機能のモック。シンプルにレスポンスの固定だけでもいいけど最近はサードパーティパッケージでもfake()形式のモックが普及しているので可能な範囲で実装。

```php
use Revolution\Copilot\Facades\Copilot;

Copilot::fake('2'); // 常に'2'を返すモック
$response = Copilot::run(prompt: '1 + 1'); // ここで実際にはCopilot CLIは呼ばれない
$this->assertEquals('2', $response->getContent());
```

Copilot::startで複数呼び出した場合。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Contracts\CopilotSession;

Copilot::fake([
    '*' => Copilot::sequence()
            ->push(Copilot::response('2'))
            ->push(Copilot::response('4')),
]);

Copilot::start(function (CopilotSession $session) use (&$response1, &$response2) {
    $response1 = $session->sendAndWait(prompt: '1 + 1'); // '2'を返す
    $response2 = $session->sendAndWait(prompt: '2 + 2'); // '4'を返す
});

$this->assertEquals('2', $response1->getContent());
```

アサーション。
特定のプロンプトが呼び出されたことを確認。

```php
Copilot::assertPrompt('1 + *');
```

プロンプトが呼び出されなかったことを確認。
```php
Copilot::assertNotPrompt('1 + *');
```

JSON-RPCリクエストを全て防止。呼び出した場合は例外`StrayRequestException`が発生。
```php
Copilot::preventStrayRequests();
```
一部のコマンドだけは許可する場合。
```php
Copilot::preventStrayRequests(allow: ['ping']);
```
防止の停止。
```php
Copilot::preventStrayRequests(false);
```
防止するのはJSON-RPCリクエストだけなのでClientのstartは防止しない。

### Phase 6: Advanced Features (後回し可能)

- [ ] ~~**6.1 Pool** - 並行セッション実行~~ Laravelの別の機能で代用できるだろうから取りやめ。
- [x] **6.2 Tool Registration** - カスタムツール登録
- [x] **6.3 Permission Handler** - 権限ハンドラー
- [ ] **6.4 MCP Server Config** - MCPサーバー設定
- [ ] **6.5 Custom Agent** - カスタムエージェント設定

---

## ファイル構成案

```
src/
├── Client.php
├── Session.php
├── CopilotSdkServiceProvider.php
├── Facades/
│   └── Copilot.php
├── JsonRpc/
│   ├── JsonRpcClient.php
│   ├── JsonRpcMessage.php
│   └── JsonRpcException.php
├── Process/
│   └── ProcessManager.php
├── Types/
│   ├── CopilotClientOptions.php
│   ├── SessionConfig.php
│   ├── MessageOptions.php
│   ├── SessionEvent.php
│   ├── SessionEventType.php
│   ├── Tool.php
│   ├── ToolInvocation.php
│   └── PermissionRequest.php
├── Testing/
│   ├── CopilotFake.php
│   └── FakeSession.php
└── Contracts/
    ├── Factory.php
    ├── CopilotClient.php
    └── CopilotSession.php

config/
└── copilot.php

tests/
├── Unit/
│   ├── JsonRpcClientTest.php
│   └── ProcessManagerTest.php
└── Feature/
    ├── CopilotClientTest.php
    └── CopilotSessionTest.php
```

---

## 技術的考慮事項

### 1. ブロッキングI/O

PHPは同期的なので、stdout/stdinの読み書きで処理がブロックされる。
- `sendAndWait()` では session.idle が来るまで読み続ける
- タイムアウト設定で無限ブロックを防ぐ
- `stream_set_timeout()` or `stream_select()` を使用

### 2. イベントハンドリング

Node.js/Pythonは非同期イベントループがあるが、PHPでは：
- `sendAndWait()` 内でイベントを収集しつつハンドラーを呼ぶ
- send() + 手動イベントポーリングの組み合わせも可能

### 3. プロセス管理

Laravel Process facade の `start()` は非同期プロセス起動に対応。
```php
$process = Process::start('copilot --server --stdio');
// $process->latestOutput() で出力取得
// $process->running() で実行中チェック
```

### 4. Content-Length パース

```php
// Header読み取り
$header = fgets($stdout);  // "Content-Length: 123\r\n"
$length = (int) explode(': ', trim($header))[1];

// 空行スキップ
fgets($stdout);

// Body読み取り
$body = fread($stdout, $length);
$message = json_decode($body, true);
```

---

## 優先度と実装順序

1. **最優先**: JsonRpcClient + ProcessManager（基盤）
2. **高**: CopilotClient + CopilotSession（コア機能）
3. **中**: Types定義（型安全性）
4. **中**: Laravel Integration（Facade, Provider, Config）
5. **低**: Artisan Commands（利便性）
6. **低**: Testing Support（テスト支援）
7. **後回し**: Advanced Features

---

## 参考

- 公式SDK: https://github.com/github/copilot-sdk
- Node.js版: `copilot-sdk/nodejs/src/`
- Python版: `copilot-sdk/python/copilot/`
