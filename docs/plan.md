# Laravel版Copilot CLI SDK

GitHub Copilot CLIへプログラムからアクセスするSDK。公式にはnode.js、Python、Go、.NETがサポートされているが、Laravel（PHP）からも利用できるようにコミュニティパッケージを作る。
https://github.com/github/copilot-sdk

まずは実装可能か調査。

`./copilot-sdk/`内がgit submoduleで追加した公式SDKのコード。

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

## Copilot CLIサーバーモード

```shell
copilot --server --port 10513 # ポートを指定してTCPトランスポートで起動
copilot --server              # ポートを指定してない場合はランダムなポートで起動
copilot --server --stdio      # TCPではなくstdioトランスポートを使用
# portとstdioは同時に指定できない
```

LaravelのProcessで非同期に起動するのが良さそう。TCPとstdioのどちらかがいいかは調査。
```php
use Illuminate\Support\Facades\Process;

$process = Process::start('copilot --server');

while ($process->running()) {
    echo $process->latestOutput();
    echo $process->latestErrorOutput();
 
    sleep(1);
}

# もしくは
# $typeは'stdout' か 'stderr'
$process = Process::start('copilot --server', function (string $type, string $output) {
    echo $output;
});

$result = $process->wait();
```

## SDK

node.js版を参考にする。

PHPでは実装できない難しいことはしてなさそう。

### CopilotClient

Copilot CLIサーバーを管理。

別で起動しているサーバーがある場合はcli_urlで指定。

- `start()`: Copilot CLIサーバーを起動。
  - `startCLIServer()`: cli_urlが指定されてない場合はプログラムからサーバーを起動、指定されていたら起動はスキップ。
  - `connectToServer()`: サーバーに接続
    - `connectViaStdio()`
    - `connectViaTcp()`
- `createSession()`: 新しい会話セッションを開始。

### CopilotSession

一つの会話。
`send()`か`sendAndWait()`でメッセージを送信。

node.jsではtypeやinterfaceを定義してオブジェクトを引数にしているけどPHPでは名前付き引数を使う。

```typescript
const response = await session.sendAndWait({ prompt: "What is 2+2?" });
```

```php
$response = $session->sendAndWait(prompt: "What is 2+2?");
```

## JSON-RPC

PHP用のcomposerパッケージはあるけど何も使わず自前実装する。
Laravel MCPはJSON-RPCサーバーを自前実装してるのでLaravelだけで大丈夫そう。
JSON-RPCクライアントなら決められたフォーマットのjsonを送信するだけなので簡単。

## 使い方

最終的な想定する使い方。

他言語のSDKを再現したレイヤーの上にLaravel流の使い方ができるFacadeやコマンドを作成する。

### 同期実行

Copilot CLIのプロンプトモードと同様の一つの処理だけしてすぐに結果を返す。

```php
$response = Copilot::run($prompt);
echo $response->output();
```

### 複数処理を実行

クロージャ内で同じセッションでの複数の処理を実行。

```php
Copilot::start(function(CopilotSession $session) use (&$response) {
    $response = $session->sendAndWait(prompt: "What is 2+2?");
});

echo $response->output();
```

node.jsではawaitを使っているけどPythonではsendAndWaitから直接responseを返しているのでPHPでも同様にする。

### 並行セッション

実現可能なら複数セッションの並行実行。`Http::pool()`や`Process::pool()`のようなイメージ。

```php
$responses = Copilot::pool(fn (Pool $pool) => [
    $pool->session(id: 'first')->sendAndWait(),
    $pool->session(id: 'second')->sendAndWait(),
    $pool->session(id: 'third')->sendAndWait(),
]);

echo $responses['first']->output();
```

### Artisan コマンド

```shell
php artisan copilot:start --port=10513
php artisan copilot:stop
```

Laravel ForgeやLaravel Cloudではバックグラウンドプロセスを起動したままにできるので`copilot:start`でcopilotサーバーを起動しておき、アプリケーションからは常に接続できる形にする。

### テスト

```php
Copilot::fake();
```

```php
Copilot::fake([
    '*' => Copilot::response(
        output:, '4',
    ),
]);
```
