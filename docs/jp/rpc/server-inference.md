# HTTP推論・ファイルシステム

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
use Illuminate\Http\Client\Response;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProfile;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveRequest;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveResult;
use Revolution\Copilot\Support\CopilotRequestContext;
use Revolution\Copilot\Support\CopilotRequestHandler;

// llmInference（experimental: モデル層へのHTTPリクエストをフック）
$client = Copilot::client([
    'request_handler' => new class extends CopilotRequestHandler
    {
        public function sendRequest(CopilotRequestContext $context, string $method, string $body): Response
        {
            $context->url = 'https://proxy.example.com/v1/chat/completions';
            $context->headers['X-Request-Source'] = ['laravel'];

            return parent::sendRequest($context, $method, $body);
        }
    },
]);

// 起動時にllmInference.setProviderが送信され、httpRequestStart / httpRequestChunkを受け取ります。
// sendRequestを上書きしないハンドラはLaravel HTTP ClientでHTTP/SSEを透過転送します。
// Closureを指定する場合はfn (CopilotRequestContext $context, string $method, string $body): Responseの形式でResponseを返します。
// request_handlerはexperimentalです。WebSocket transportは未対応で、501相当のエラー応答になります。

// sessionFs (セッションファイルシステムプロバイダーの登録)
Copilot::client()->rpc()->sessionFs()->setProvider(new SessionFsSetProviderRequest(
    initialCwd: '/path/to/project',
    sessionStatePath: '.copilot/sessions',
    conventions: 'posix',
));

// 拡張機能の起動方法を解決するクライアントオプション（応答は15秒以内）
$client = Copilot::client([
    'extensionLaunchProvider' => fn (ExtensionLaunchProviderResolveRequest $request) => new ExtensionLaunchProviderResolveResult(
        launch: new ExtensionLaunchProfile(
            executable: '/usr/bin/node',
            args: [$request->modulePath],
        ),
    ),
]);
// 対応する起動方法がない場合はnullを返します。
// fn (ExtensionLaunchProviderResolveRequest $request) => null

$sessions = Copilot::client()->rpc()->sessions();
```

## fetch のプロキシ経路（experimental）

`fetch()->resolveProxy()`は、対象URLに対して`fetch()->send()`が使用する候補プロキシ経路を、対象URLへ接続せずに確認します。PACスクリプトの取得は行われる場合がありますが、対象のURLには接続しません。認証情報は返されないため、プロキシ経由で接続するクライアントは別途認証処理を行う必要があります。

```php
use Revolution\Copilot\Facades\Copilot;

$result = Copilot::client()->rpc()->fetch()->resolveProxy([
    'url' => 'https://example.com/resource',
]);

foreach ($result->routes as $route) {
    // proxyUrlがない場合は直接接続
    dump($route->proxyUrl);
}

dump($result->source);
```

## network (experimental: ネットワークコールバック)

`network()->setProvider()`で、クライアント側に登録したプロキシ解決・Kerberos認証コールバックをランタイムに通知します。通常は`Client`の`network`オプションから自動登録されるため、直接呼び出す必要はありません。`NetworkSetProviderRequest`のコールバック項目は、対応するハンドラーが登録済みであることを示します。RPCの戻り値はありません。利用するランタイムは`network.setProvider` RPCをサポートしている必要があり、未対応の場合はクライアント起動時に更新を案内する例外が発生します。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Enums\NetworkScope;
use Revolution\Copilot\Types\Rpc\NetworkSetProviderRequest;

Copilot::client()->rpc()->network()->setProvider(
    new NetworkSetProviderRequest(
        scope: NetworkScope::Runtime,
        resolveProxy: true,
        authenticateNegotiate: true,
    ),
);
```

`scope`は省略すると`connection`です。`runtime`は共有バックグラウンド通信も対象にしますが、同時にこのscopeを登録できる接続は1つだけです。
