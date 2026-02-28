# MCP

SessionConfigの`mcpServers`にMCPサーバーを指定すれば使うことができます。

Copilot CLIでLaravel Boostを使う場合は [laravel-boost-copilot-cli](https://github.com/invokable/laravel-boost-copilot-cli) も使いましょう。
laravel-boost-copilot-cliを作った知見から`'type' => 'local'`と`'tools' => ['*']`が必須なことが分かっています。これがないとMCPサーバーとして認識されません。

```php
Artisan::command('copilot:mcp', function () {
    $config = new SessionConfig(
        mcpServers: [
            'laravel-boost' => [
                'type' => 'local',
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
