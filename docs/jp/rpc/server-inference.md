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
