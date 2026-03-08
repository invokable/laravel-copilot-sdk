# Permission Request

## Auto-approve (デフォルト)

`config/copilot.php`で`permission_approve`が`true`（デフォルト）の場合、`Copilot::run()`や`Copilot::start()`を使う時は自動的にすべてのPermission Requestが許可されます。（ただし危険性の高い`shell`, `write`は除きます）

公式SDKはすべて拒否がデフォルトですがLaravel版では `Copilot::run()`, `Copilot::start()` で使う時の利便性を優先して許可にしています。

> [!CAUTION]
> ユーザーからのプロンプト入力を許可する使い方の場合は、自動許可は危険なので必ずfalseにしてください。
> readでLaravelプロジェクトのコードを読めるだけでも危険です。

```php
// config/copilot.php
'permission_approve' => env('COPILOT_PERMISSION_APPROVE', true),
```

この設定が有効な場合、`onPermissionRequest`を明示的に指定しなくても`PermissionHandler::approveSafety()`が自動的に使われます。

```php
use Revolution\Copilot\Facades\Copilot;

// onPermissionRequestを指定しなくても自動的に全許可
$response = Copilot::run(prompt: 'Hello');
```

公式SDKと同様にデフォルトで拒否したい場合は`false`に設定します。

```php
// .env
COPILOT_PERMISSION_APPROVE=false,
```

この場合は`onPermissionRequest`の指定が必須になります。

## PermissionHandler::approveAll()

すべてのリクエストを自動的に許可する場合は `PermissionHandler::approveAll()` を使います。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    onPermissionRequest: PermissionHandler::approveAll(),
);

$response = Copilot::run(prompt: 'Hello', config: $config);
```

## PermissionHandler::approveSafety()

危険性の高い`shell`, `write`のみ拒否して他を自動的に許可する場合は `PermissionHandler::approveSafety()` を使います。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    onPermissionRequest: PermissionHandler::approveSafety(),
);

$response = Copilot::run(prompt: 'Hello', config: $config);
```

これでも完全に安全とは限らないので細かく制御したい場合は`$request['kind']`を見て判定するカスタムハンドラを書いて対応してください。

## PermissionHandler::denyAll()

すべてを拒否する場合は`PermissionHandler::denyAll()`を使います。

```php
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    onPermissionRequest: PermissionHandler::denyAll(),
);
```

## Clientの直接使用

`CopilotClient`を直接使用する場合は、公式SDK同様に`onPermissionRequest`の指定が**必須**です。

```php
use Revolution\Copilot\Client;
use Revolution\Copilot\Support\PermissionHandler;

$client = new Client([
    'cli_path' => 'copilot',
    'cli_args' => [],
    'cwd' => base_path(),
    'log_level' => 'info',
    'env' => null,
]);
$client->start();

// onPermissionRequestが必須
$session = $client->createSession([
    'onPermissionRequest' => PermissionHandler::approveSafety(),
]);

// 指定しないとInvalidArgumentExceptionがスローされる
// $session = $client->createSession([]); // Error!
```

## カスタムハンドラ

リクエストの種類に応じて個別に許可・拒否を制御する場合は、クロージャを指定します。`$request`と`$invocation`は下記のような内容の配列です。

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

`kind`と`toolCallId`以外はkindによって内容が異なります。
```
kind: "shell" | "write" | "mcp" | "read" | "url" | "custom-tool"
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

許可・拒否の結果を配列で返します。`PermissionRequestResultKind` クラスを使うと便利です。

```php
return PermissionRequestResultKind::approved();
return PermissionRequestResultKind::deniedInteractivelyByUser();
```

## プロトコルの詳細

Protocol v3（現在のデフォルト）では、パーミッションリクエストはJSON-RPCリクエストではなくセッションイベント（`permission.requested`）としてブロードキャストされます。SDKはこのイベントを内部的に処理して `session.permissions.handlePendingPermissionRequest` RPCで応答します。

**`SessionConfig` の使い方は変わりません。** `onPermissionRequest` にハンドラを渡すだけで、プロトコルの違いはSDKが吸収します。

## PermissionRequestResultKind

`['kind' => 'approved']`の形式で返せばよいですが分かりにくいので`PermissionRequestResultKind`クラスも用意しています。

```php
use Revolution\Copilot\Support\PermissionRequestResultKind;

$confirm = confirm(
    label: 'Do you accept the requested permissions?',
);

if ($confirm) {
    return PermissionRequestResultKind::approved();
} else {
    return PermissionRequestResultKind::deniedInteractivelyByUser();
}
```

`Laravel\Prompts\confirm`ではなく`Laravel\Prompts\select`を使いたい場合は`PermissionRequestResultKind::select()`で選択肢を取得できます。

```php
use Revolution\Copilot\Support\PermissionRequestResultKind;
use function Laravel\Prompts\select;

$select = select(
    label: 'Do you accept the requested permissions?',
    options: PermissionRequestResultKind::select(),
);

return ['kind' => $select];
```
