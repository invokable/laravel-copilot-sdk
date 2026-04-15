# MCP

SessionConfigの`mcpServers`にMCPサーバーを指定すれば使うことができます。

Copilot CLIでLaravel Boostを使う場合は [laravel-boost-copilot-cli](https://github.com/invokable/laravel-boost-copilot-cli) も使いましょう。
`'type' => 'stdio'`（または`'local'`）と`'tools' => ['*']`が必須なことが分かっています。これがないとMCPサーバーとして認識されません。

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
