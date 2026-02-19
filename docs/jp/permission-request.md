# Permission Request

## Deny by Default

ツールの操作（ファイル書き込み、シェルコマンド、URL取得、MCP呼び出しなど）はデフォルトで**拒否**される。許可するには `onPermissionRequest` ハンドラを指定する必要がある。

## PermissionHandler::approveAll()

すべてのリクエストを自動的に許可する場合は `PermissionHandler::approveAll()` を使う。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    onPermissionRequest: PermissionHandler::approveAll(),
);

$response = Copilot::run(prompt: 'Hello', config: $config);
```

## カスタムハンドラ

リクエストの種類に応じて個別に許可・拒否を制御する場合は、クロージャを指定する。`$request`と`$invocation`は下記のような内容の配列。

```php
use Illuminate\Support\Facades\Artisan;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;

use function Laravel\Prompts\{confirm, note, spin, text};

Artisan::command('copilot:chat', function () {
    $config = new SessionConfig(
        onPermissionRequest: function (array $request, array $invocation) {
            $confirm = confirm(
                label: 'Do you accept the requested permissions?',
            );
            if ($confirm) {
                return ['kind' => 'approved'];
            } else {
                return ['kind' => 'denied-interactively-by-user'];
            }
        },
    );

    Copilot::start(function (CopilotSession $session) use ($config) {
        while (true) {
            $prompt = text(
                label: 'Enter your prompt',
                placeholder: 'Ask me anything...',
                required: true,
                hint: 'Ctrl+C to exit',
            );

            $response = spin(
                callback: fn () => $session->sendAndWait($prompt),
                message: 'Waiting for Copilot response...',
            );

            note($response->content());
        }
    }, config: $config);
});
```

### $request

`kind`と`toolCallId`以外はkindによって内容が異なる。
```
kind: "shell" | "write" | "mcp" | "read" | "url"
```

```php
[
  "kind" => "shell",
  "toolCallId" => "toolu_...",
  "fullCommandText" => "...",
  "intention" => "Run copilot:ping to test permission request",
  "commands" => [
    [
      "identifier" => "bash",
      "readOnly" => false,
    ]
  ]
  "possiblePaths" => [],
  "possibleUrls" => [],
  "hasWriteFileRedirection" => false,
  "canOfferSessionApproval" => false,
]
```

### $invocation
```php
[
  "sessionId" => "...",
]
```

## レスポンス

許可した場合のJSON-RPCレスポンス例。idが0だったりresultが二重だけどこうしないと成功しなかった。

```json
{"jsonrpc":"2.0","id":0,"result":{"result":{"kind":"approved"}}}
``` 

## PermissionRequestKind

`['kind' => 'approved']`の形式で返せばいいけど分かりにくいのでPermissionRequestKindクラスも用意。

```php
use Revolution\Copilot\Support\PermissionRequestKind;

$confirm = confirm(
    label: 'Do you accept the requested permissions?',
);

if ($confirm) {
    return PermissionRequestKind::approved();
} else {
    return PermissionRequestKind::deniedInteractivelyByUser();
}
```

`Laravel\Prompts\confirm`ではなく`Laravel\Prompts\select`を使いたい場合は`PermissionRequestKind::select()`で4つの選択肢を取得できる。

```php
use Revolution\Copilot\Support\PermissionRequestKind;
use function Laravel\Prompts\select;

$select = select(
    label: 'Do you accept the requested permissions?',
    options: PermissionRequestKind::select(),
);

return ['kind' => $select];
```
