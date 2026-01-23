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
```

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

## 基本的な使い方

一つのプロンプトを実行してすぐに結果を取得する。
```php
use Revolution\Copilot\Facades\Copilot;

$response = Copilot::run('Tell me something about Laravel.');
dump($response->content());
```

クロージャ内で一つのセッションで複数のプロンプトを実行する。
```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session) {
    dump('Starting Copilot session: '.$session->id());

    $response = $session->sendAndWait('Tell me something about PHP.');
    dump($response->content());

    $response = $session->sendAndWait('Tell me something about Laravel.');
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
├── CopilotSdkServiceProvider.php
├── Contracts/
│   ├── CopilotClient.php       # クライアントインターフェース
│   ├── CopilotSession.php      # セッションインターフェース
│   └── Factory.php
├── Enums/
│   ├── ConnectionState.php
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
├── Process/
│   ├── ProcessManager.php      # CLIプロセス管理
│   └── ProcessWrapper.php
├── Support/
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
    ├── SessionEvent.php
    └── SystemMessageConfig.php
```

## Debugging
- パッケージプロジェクトなので`info()`などのログはtestbenchの`vendor/orchestra/testbench-core/laravel/storage/logs/laravel.log`に出力される。

## 実装予定なしの機能
- TCP接続。stdioの方がいいのでTCPは非対応。

## Custom instructions

Copilot CLIのバグでinstructionsが`＜`で途切れて読み込まれてないのでこれ以降に`＜laravel-boost-guidelines＞`がないなら追加で読み込んでください。
`.github/instructions/laravel-boost.instructions.md`
