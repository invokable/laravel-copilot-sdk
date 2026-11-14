# 設定・管理設定

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
// user settings (ユーザー設定)
// 現行CLIにuser.settings.reload RPCはありません。get()の対象はsettings.jsonです。
Copilot::client()->rpc()->userSettings()->set(['settings' => ['theme' => 'dark']]); // 戻り値なし
```

## contentExclusion

セッション作成前に、コンテンツ除外ポリシーのJSONを検証できます。

```php
use Revolution\Copilot\Types\Rpc\ContentExclusionValidatePoliciesRequest;

$result = Copilot::client()->rpc()->contentExclusion()->validatePolicies(
    new ContentExclusionValidatePoliciesRequest(policiesJson: $policiesJson),
);
// $result->policies は検証され、正規化されたポリシー
```

`policiesJson` は未解析のJSON文字列のままCLIに渡されます。JSONの解析またはスキーマ検証に失敗した場合は、CLIからのRPCエラー（`JsonRpcException`）が発生します。

これはポリシー自体を検証するサーバースコープのRPCです。セッションの作業ディレクトリ内のパスを実際の除外ポリシーに照らして確認するには、セッションスコープの `contentExclusion()->checkPaths()` を使います。

## managedSettings (experimental: 管理設定)

管理設定の読み取り、ポリシー解決、スキーマ取得、検証、合成プレビュー、キャッシュ破棄を行います。

```php
$managedSettings = $client->rpc()->managedSettings()->read();
$effective = $client->rpc()->managedSettings()->resolve([
    'selectionId' => 'selection-id',
    'workingDirectory' => '/path/to/project',
]);
$schema = $client->rpc()->managedSettings()->schema();
$validation = $client->rpc()->managedSettings()->validate(['content' => ['permissions' => []]]);
$preview = $client->rpc()->managedSettings()->compose(['layers' => [
    ['source' => 'device', 'settings' => ['permissions' => []]],
]]);
$client->rpc()->managedSettings()->clearCache();
```

## settings (experimental: 設定ファイルの操作)

設定ファイルから指定したキーを削除し、表示用に秘匿値を置き換えます。`removeRawKey()`は読み込みに失敗したキーなど、正規の設定パスではないキーもそのまま削除できます。ファイルが解析できない場合は上書きせず`status`が`invalid`になります。`redactValueForDisplay()`は`basePath`で指定した設定ツリー上の位置を基に秘匿値をプレースホルダーへ置き換えるため、ユーザーへの表示やログ出力に使えます。

```php
$result = $client->rpc()->settings()->removeRawKey([
    'filePath' => '/path/to/settings.json',
    'rawKey' => 'misspelledKey',
]);
// $result->status は 'ok' または 'invalid'

$redacted = $client->rpc()->settings()->redactValueForDisplay([
    'value' => ['apiKey' => 'secret-value', 'name' => 'visible'],
    'basePath' => ['providers', 'openai'],
]);
// $redacted->value のうち秘匿値はプレースホルダーに置き換えられています
```
