# Copilot CLI SDK for Laravel Project Guidelines

## Overview

GitHub Copilot CLIへプログラムからアクセスするSDK。公式にはnode.js、Python、Go、.NETがサポートされているが、Laravel（PHP）からも利用できるようにするコミュニティパッケージ。

## 公式 SDK
https://github.com/github/copilot-sdk

`./copilot-sdk/`内はgit submoduleで追加した公式SDKのコード。
公式SDKのコードを確認する時は以下のコマンドで最新に更新してください。
```shell
git submodule update --remote --merge
```

## Technology Stack

- **Language**: PHP 8.4+
- **Framework**: Laravel 12.x+
- **Testing**: Pest PHP 4.x
- **Code Quality**: Laravel Pint (PSR-12)

## Commands
```shell
composer run test        # Run tests with Pest
composer run lint        # Run Laravel Pint for code style checks
```

実際にCopilot CLIを起動する動作確認コマンド。
```shell
vendor/bin/testbench copilot:ping
vendor/bin/testbench copilot:version
```

開発時用の動作確認コマンドは`workbench/routes/console.php`で定義。

## Architecture

`copilot --server`でCopilot CLI自身がサーバーとして起動。
すべての SDK は JSON-RPC を介して Copilot CLI サーバーと通信。

```
Your Application
       ↓
  SDK Client
       ↓ JSON-RPC
  Copilot CLI (server mode)
```

ClientやSessionは公式SDKを再現しつつLaravel流にCopilot Facadeを中心にした使い方。

### JSON-RPC

stdioやTCPを使ってJSON-RPC 2.0で通信。

実際の生データはContent-Length付きの以下のようなフォーマット。

リクエスト
```
Content-Length: {length}\r\n\r\n{"jsonrpc":"2.0","id":"uuid","method":"method.name","params":{}}
```

レスポンス
```
Content-Length: {length}\r\n\r\n{"jsonrpc":"2.0","id":"uuid","result":{}}
```

`StdioTransport`で一時的にブロッキングモードに切り替えてヘッダーを読み取っているのはこうしないと正常に読み取れないから。「fgets()を使う時はブロッキングモードにする」と覚えて今後の実装時にも注意する。

```php
// Temporarily set blocking mode for reading
stream_set_blocking($this->stdout, true);

// Read header line
$headerLine = fgets($this->stdout);
```

## 基本的な使い方

一つのプロンプトを実行してすぐに結果を取得する。
```php
use Revolution\Copilot\Facades\Copilot;

$response = Copilot::run(prompt: 'Tell me something about Laravel.');
dump($response->content());
```

クロージャ内で一つのセッションで複数のプロンプトを実行する。
```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session) {
    dump('Starting Copilot session: '.$session->id());

    $response = $session->sendAndWait(prompt: 'Tell me something about PHP.');
    dump($response->content());

    $response = $session->sendAndWait(prompt: 'Tell me something about Laravel.');
    dump($response->content());
});
```

## Directory Structure

- 公式SDKのアップデートに合わせて更新する時は、Clientなどの実装とContractsのinterface、Testingのテスト用クラスが正しく更新されていることを確認。
- Node.jsのtypes.tsで定義されている型はTypesディレクトリにreadonly classとして作成。`Illuminate\Contracts\Support\Arrayable`インターフェイスを実装し`fromArray()`と`toArray()`を持つ共通仕様。
- `copilot-sdk/nodejs/src/generated/session-events.ts`のtypeは`src/Enums/SessionEventType.php`のEnumで定義。

```
src/
├── Client.php                  # CopilotClient実装
├── Session.php                 # CopilotSession実装
├── CopilotManager.php          # Factory実装
├── Protocol.php                # SDK_PROTOCOL_VERSIONを定義。copilot cli側が更新されたらここを更新。
├── helpers.php                 # copilot()ヘルパー関数。あくまでヘルパーなので複雑な機能は追加しない。
├── CopilotSdkServiceProvider.php
├── Contracts/
│   ├── CopilotClient.php       # クライアントインターフェース
│   ├── CopilotSession.php      # セッションインターフェース
│   ├── Transport.php
│   └── Factory.php             # CopilotManagerのインターフェース
├── Enums/
│   ├── ConnectionState.php
│   ├── ReasoningEffort.php
│   └── SessionEventType.php
├── Events/
│   ├── Client/
│   │   ├── ClientStarted.php
│   │   └── PingPong.php
│   ├── JsonRpc/
│   │   ├── MessageReceived.php
│   │   ├── MessageSending.php
│   │   └── ResponseReceived.php
│   ├── Process/
│   │   └── ProcessStarted.php
│   └── Session/
│       ├── CreateSession.php
│       ├── MessageSend.php
│       ├── MessageSendAndWait.php
│       └── ResumeSession.php
├── Exceptions/
│   ├── JsonRpcException.php
│   └── StrayRequestException.php
├── Facades/
│   └── Copilot.php             # Laravelファサード
├── JsonRpc/
│   ├── JsonRpcClient.php       # JSON-RPC 2.0クライアント
│   └── JsonRpcMessage.php
├── Transport/
│   ├── StdioTransport.php
│   └── TcpTransport.php
├── Process/
│   ├── ProcessManager.php      # CLIプロセス管理
│   └── ProcessWrapper.php
├── Support/                     # 分類しにくいヘルパークラス
│   ├── Attachment.php
│   └── PermissionRequestKind.php
├── Testing/
│   ├── CopilotFake.php         # テスト用モック
│   ├── FakeSession.php
│   ├── ResponseSequence.php
│   └── WithFake.php
└── Types/
    ├── ProviderConfig.php
    ├── ResumeSessionConfig.php
    ├── SessionConfig.php
    ├── SessionEvent.php        # Laravel用に強化したSessionEventクラス
    ├── SystemMessageConfig.php
    └── ...other types...
```

### Types
基本的には公式SDKに合わせてクラスを作成して使う方式。  
ただし頻繁に使う箇所は利便性を優先して名前付き引数を使う。

```php
$response = Copilot::run(prompt: '');
$response = $session->sendAndWait(prompt: '');
$message_id = $session->send(prompt: '');

// こういう使い方にはしない
Copilot::run(['prompt' => '']);
Copilot::run(new MessageOptions(prompt: ''));
```

名前付き引数のほうがJavaScriptの使い勝手を再現できる。
```javascript
const response = await session.sendAndWait({ prompt: "What is 2 + 2?" });
```

よく使うけど項目が多い箇所はクラスでも配列でも受け入れる。

```php
Copilot::run(prompt: 'What is 2 + 2?', config: new SessionConfig(model: 'gpt-5'));
Copilot::run(prompt: 'What is 2 + 2?', config: ['model' => 'gpt-5']);
```

## ドキュメント
- README.md: パッケージの概要と基本的な使い方。英語。`Copilot::run` `Copilot::start` Testing機能はLaravel版特有なのでこれをメインに説明。
- `docs/getting-started.md` 公式SDKのgetting-started.mdと同様のLaravel版。英語。
- `docs/jp/`以下に日本語ドキュメントを配置。英語の詳細なドキュメントは公式SDKにあるので日本語を中心に用意する。読めない言語でもCopilotに聞けばいいんだから。公式SDKのドキュメントとは関係なく細かい機能のドキュメントを書いていく。いずれまとめて英訳。

### docs/jp/
- `ask-user.md`(AI): User Input Requests。公式から翻訳。
- `attachments.md`: File Attachments用のヘルパー。
- `basic-usage.md`: Laravel流ではない公式SDKに沿った使い方
- `currency.md`: Laravel の Concurrency を使った並行実行
- `custom-provider.md`(AI): カスタムプロバイダー（BYOK - Bring Your Own Key）の使い方。公式から翻訳。
- `events.md`: Laravel Event
- `fake.md`: テスト用機能の使い方
- `github-actions.md`: GitHub Actionsでの使い方
- `github-token.md`: GitHubトークンの実行時切り替え
- `hooks.md`(AI): Session Hooks。公式から翻訳。
- `laravel-cloud.md`: Laravel Cloudでの使い方
- `mcp.md`: MCPサーバーの使い方
- `models.md`: 利用可能なモデル一覧
- `permission-requests.md`: Permission Requests
- `prompt.md`: プロンプト
- `resume.md`: セッションの再開方法
- `send-on.md`: Session `on()`で途中のメッセージを受け取る
- `session-event.md`: Laravel用に強化したSessionEventクラスの詳細
- `streaming.md`: Streamingの使い方
- `tcp-mode.md`(AI): TCPモードの使い方
- `tools.md`: ツールの使い方

## Debugging
- パッケージプロジェクトなので`info()`などのログはtestbenchの`vendor/orchestra/testbench-core/laravel/storage/logs/laravel.log`に出力される。

## Custom instructions

Copilot CLIのバグでinstructionsが`＜`で途切れて読み込まれてないのでこれ以降に`＜laravel-boost-guidelines＞`がないなら追加で読み込んでください。
`.github/instructions/laravel-boost.instructions.md`
