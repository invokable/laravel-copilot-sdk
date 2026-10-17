# MCP

SessionConfigの`mcpServers`にMCPサーバーを指定すれば使うことができます。

Copilot CLIでLaravel Boostを使う場合は [laravel-boost-copilot-cli](https://github.com/invokable/laravel-boost-copilot-cli) も使いましょう。
`'type' => 'stdio'`（または`'local'`）と`'tools' => ['*']`が必須なことが分かっています。これがないとMCPサーバーとして認識されません。
`args`は任意です（省略可能）。

```php
Artisan::command('copilot:mcp', function () {
    $config = new SessionConfig(
        mcpServers: [
            'laravel-boost' => [
                'type' => 'stdio',
                'command' => 'php',
                'args' => ['artisan', 'boost:mcp'],
                'tools' => ['*'],
            ],
        ],
    );

    Copilot::start(function (CopilotSession $session) {
        info('Starting Copilot with Laravel Boost MCP: '.$session->id());

        $prompt = 'どのMCPが読み込まれている？ laravel-boostが使える場合はapplication-infoでアプリ情報を取得して。';

        warning($prompt);

        $response = spin(
            callback: fn () => $session->sendAndWait($prompt),
            message: 'Thinking...',
        );

        note($response->content());
    }, config: $config);
});
```

## MCP Apps（experimental）

`enableMcpApps`を有効にしたセッションでは、`$session->rpc()->mcp()->apps()`からMCP Appsのツールやリソースを操作できます。`listTools()`でアプリから呼び出せるツールを取得し、`callTool()`でツールを実行し、`readResource()`で`ui://`リソース（HTMLなど）を取得します。ツール一覧と呼び出しには、呼び出し元サーバーを示す`originServerName`が必要です。

`getHostContext()` / `setHostContext()`では、テーマやロケールなどホストコンテキストを読み書きできます。`diagnose()`はサーバーのMCP Apps対応状態を返します。

SDKはデータ取得・設定用のRPCを提供しますが、取得したHTMLの描画やiframeの管理は行いません。これらはLivewireやInertiaなど呼び出し側アプリケーションで実装してください。詳細なコード例は[RPCドキュメント](./rpc.md#mcp-apps-experimental)を参照してください。

## MCP OAuthトークンをホストから渡す

MCPサーバーがOAuth認証を要求する場合は、`onMcpAuthRequest`でホスト側の認証処理を指定できます。コールバックにはOAuthリクエスト情報と`sessionId`が渡され、トークンを返すとCLIランタイムに渡されます。`null`または`McpAuthResult`の`cancelled`を返すと認証をキャンセルします。

```php
use Revolution\Copilot\Types\McpAuthRequest;
use Revolution\Copilot\Types\McpAuthResult;
use Revolution\Copilot\Types\McpAuthToken;
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    mcpServers: [
        'private-mcp' => [
            'type' => 'http',
            'url' => 'https://mcp.example.com',
        ],
    ],
    onMcpAuthRequest: function (McpAuthRequest $request, array $context): McpAuthToken|McpAuthResult|null {
        // アプリケーション独自の認証サービスからトークンを取得
        $accessToken = $credentialProvider->tokenFor($request->serverName);

        return $accessToken === null
            ? new McpAuthResult(kind: 'cancelled')
            : new McpAuthToken(accessToken: $accessToken, tokenType: 'Bearer');
    },
);
```

ハンドラーを指定すると、`mcp.oauth_required`イベントの受信登録も自動で行われます。ブラウザーを開けないWebリクエスト、キュー、バッチなどでもホスト側で認証できます。セッション再開時も`ResumeSessionConfig`に同じハンドラーを指定してください。

## MCP OAuthの事前確認（Server RPC）

リモートMCPサーバーがOAuth認証を要求した後、Server RPCでDynamic Client Registration（DCR）に対応しているかを調べられます。`authorizationServerUrl`はメタデータから認可サーバーを発見できた場合に返ります。`supportsDcr`が`false`なら動的登録は利用できません。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\McpOauthCheckDcrSupportRequest;
use Revolution\Copilot\Types\Rpc\McpOauthIsMicrosoftAuthorizationServerRequest;

$oauth = Copilot::client()->rpc()->mcp()->oauth();

$dcr = $oauth->checkDcrSupport(new McpOauthCheckDcrSupportRequest(
    serverUrl: 'https://mcp.example.com',
    resourceMetadataText: $challengeMetadata,
    sessionId: $session->id(),
));

$isMicrosoft = $dcr->authorizationServerUrl === null
    ? false
    : $oauth->isMicrosoftAuthorizationServer(new McpOauthIsMicrosoftAuthorizationServerRequest(
        authorizationServerUrl: $dcr->authorizationServerUrl,
    ))->isMicrosoftAuthorizationServer;
```

`resourceMetadataText`は`WWW-Authenticate`チャレンジから得た保護リソースメタデータで、省略するとサーバーURLから認可サーバーを探索します。`sessionId`を指定すると、そのローカルセッションの送信先ポリシーとsandbox proxyが適用されます。Microsoft Entra IDはDCRに対応しないため、Microsoftの認可サーバーと判定された場合は、ユーザーにクライアント資格情報を求める代わりに事前登録済みクライアントを使う選択肢があります。

これらはServer RPC（`Copilot::client()->rpc()`）です。セッションRPCの`$session->rpc()->mcp()->oauth()`が提供するログインやOAuth要求の処理とは別の名前空間です。

## MCPサーバーのステータス

セッション内のMCPサーバーのステータスは以下の値を取ります：

| ステータス | 説明 |
|---|---|
| `connected` | 接続済み |
| `failed` | 接続失敗 |
| `needs-auth` | 認証が必要 |
| `pending` | 接続待ち |
| `disabled` | 無効化済み |
| `not_configured` | 未設定 |

`$session->rpc()->mcp()->listConfigured()` の各サーバーには、実行時に観測できた状態が `live` として含まれることがあります。`McpConfiguredServerState` の `toolsLoaded` は現在の接続でツール一覧が読み込まれたかを表す任意のboolです。未報告の場合は省略されるため、`false`（未読み込み）と区別してください。`live` がある場合は `McpConfiguredServerState::fromArray($server['live'])` で型付き状態として扱えます。接続後もツール読み込み中かどうかは [`session.mcp_server_tools_loaded`](./session-event.md) イベントで確認できます。

## MCPサーバー設定の管理（Server RPC）

セッションとは別に、サーバーレベルでMCPサーバーの設定を管理できます。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\McpConfigAddRequest;
use Revolution\Copilot\Types\Rpc\McpConfigUpdateRequest;
use Revolution\Copilot\Types\Rpc\McpConfigRemoveRequest;
use Revolution\Copilot\Types\Rpc\McpServerValue;

// 設定済みのMCPサーバー一覧を取得
$result = Copilot::client()->rpc()->mcp()->list();
foreach ($result->servers as $name => $config) {
    dump($name, $config->type, $config->command);
}

// MCPサーバーを追加
Copilot::client()->rpc()->mcp()->add(new McpConfigAddRequest(
    name: 'laravel-boost',
    config: new McpServerValue(
        type: 'stdio',
        command: 'php',
        args: ['artisan', 'boost:mcp'],
        tools: ['*'],
    ),
));

// MCPサーバーの設定を更新
Copilot::client()->rpc()->mcp()->update(new McpConfigUpdateRequest(
    name: 'laravel-boost',
    config: new McpServerValue(
        type: 'stdio',
        command: 'php',
        args: ['artisan', 'boost:mcp', '--verbose'],
        tools: ['*'],
    ),
));

// MCPサーバーを削除
Copilot::client()->rpc()->mcp()->remove(new McpConfigRemoveRequest(name: 'laravel-boost'));
```

## MCP再読み込み時の無効化設定

`reloadWithConfig()`で設定を渡す場合、`configuredDisabledServers`は呼び出し側が解決した設定由来の無効化リストです。セッション単位の`disabledServers`やCLIの`cliEnabledServers`とは別に指定してください。設定由来の無効化はCLIによる有効化と明示的なセッション無効化より優先度が低いため、`configuredDisabledServers`を`disabledServers`へまとめないでください。

```php
$session->rpc()->mcp()->reloadWithConfig([
    'config' => [
        'mcpServers' => $mcpServers,
        'disabledServers' => ['session-disabled'],
        'cliEnabledServers' => ['cli-enabled'],
        'configuredDisabledServers' => ['settings-disabled'],
    ],
]);
```
