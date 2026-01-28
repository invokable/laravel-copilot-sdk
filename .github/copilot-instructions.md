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
├── CopilotSdkServiceProvider.php
├── Contracts/
│   ├── CopilotClient.php       # クライアントインターフェース
│   ├── CopilotSession.php      # セッションインターフェース
│   ├── Transport.php
│   └── Factory.php             # CopilotManagerのインターフェース
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
├── Transport/
│   ├── StdioTransport.php
│   └── TcpTransport.php
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

## Testing
- `tests`以下にPestでテストコードを配置。
- `tests/Unit`にユニットテスト、`tests/Feature`に機能テストを配置。
- `tests/E2E`は実際にCopilot CLIを起動するテスト。プロンプトを送信しない=プレミアムリクエストを消費しない範囲ならE2Eテストも実行可能。プレミアムリクエストが不要な無料モデルを指定すればプロンプト送信も可能かもしれない。

### Copilot::fake()

`Copilot::fake()`はCopilot Facadeから使う機能のためのモック。他の機能はモックしない。

```php
use Revolution\Copilot\Facades\Copilot;

Copilot::fake('2'); // 常に'2'を返すモック

$response = Copilot::run(prompt: '1 + 1'); // ここで実際にはCopilot CLIは呼ばれない

// Pest
expect($response->content())->toBe('2');
// PHPUnit
$this->assertEquals('2', $response->content());
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

expect($response1->content())->toBe('2');
```

### アサーション

特定のプロンプトが呼び出されたことを確認。

```php
Copilot::assertPrompt('1 + *');
```

プロンプトが呼び出されなかったことを確認。
```php
Copilot::assertNotPrompt('1 + *');
```

Assert the number of prompts called.

```php
Copilot::assertPromptCount(3);
```

Assert that no prompts were called.

```php
Copilot::assertNothingSent();
```

### Prevent stray requests

JSON-RPCリクエストを全て防止。呼び出した場合は例外`Revolution\Copilot\Exceptions\StrayRequestException`が発生。

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

## ドキュメント
- README.md: パッケージの概要と基本的な使い方。英語。`Copilot::run` `Copilot::start` Testing機能はLaravel版特有なのでこれをメインに説明。
- `docs/getting-started.md` 公式SDKのgetting-started.mdと同様のLaravel版。英語。
- `docs/jp/`以下に日本語ドキュメントを配置。英語の詳細なドキュメントは公式SDKにあるので日本語を中心に用意する。読めない言語でもCopilotに聞けばいいんだから。公式SDKのドキュメントとは関係なく細かい機能のドキュメントを書いていく。いずれまとめて英訳。

## Debugging
- パッケージプロジェクトなので`info()`などのログはtestbenchの`vendor/orchestra/testbench-core/laravel/storage/logs/laravel.log`に出力される。

## TODO

- ProcessManagerやJsonRpcClientはPHPの機能を直接使っていて効率は最も良いけどテストしにくい。LaravelやSymfonyのProcessはstdioに直接アクセスする方法が用意されてないのでこの辺りはいずれ検討。
  - JsonRpcClientはテストが難しい箇所をStdioTransportに分けて解決。

## Custom instructions

Copilot CLIのバグでinstructionsが`＜`で途切れて読み込まれてないのでこれ以降に`＜laravel-boost-guidelines＞`がないなら追加で読み込んでください。
`.github/instructions/laravel-boost.instructions.md`
