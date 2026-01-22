# Permission Request

## 基本的な使い方

SessionConfigの `onPermissionRequest` にクロージャを指定するとCopilotから権限を要求された時に呼び出される。`$request`と`$invocation`は下記のような内容の配列。

```php
use function Laravel\Prompts\confirm;

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
