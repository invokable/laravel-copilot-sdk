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

## providersConfig (experimental: プロバイダー設定)

`providersConfig()->addModel()`はサーバーの保存済みプロバイダー設定にモデルを追加する実験的なRPCです。プロバイダーが未保存の場合は作成され、`providerAdded`で確認できます。永続的な操作ですが、実行中のセッションは変更しません。

```php
use Revolution\Copilot\Facades\Copilot;

$result = Copilot::client()->rpc()->providersConfig()->addModel([
    'provider' => ['name' => 'my-provider', 'baseUrl' => 'https://example.com/v1'],
    'model' => ['id' => 'my-model', 'provider' => 'my-provider'],
    // 'credential' => $apiKey,
]);

// $result->providerAdded
```

配列の代わりに`ProvidersConfigAddModelRequest`を渡せます。任意の`credential`はCLIによってOSキーチェーンに保存され、結果には返されません。

同じ`providersConfig()`から、保存済みの設定を読み取り・更新・削除できます。これらも実験的なAPIであり、実行中のセッションは変更しません。

```php
$config = Copilot::client()->rpc()->providersConfig();
$saved = $config->read(); // $saved->providers と $saved->models

$provider = $config->updateProvider([
    'provider' => ['name' => 'my-provider', 'baseUrl' => 'https://example.com/v2'],
    // 'credential' => $apiKey,
]); // $provider->providerName

$model = $config->updateModel([
    'model' => ['id' => 'my-model', 'provider' => 'my-provider', 'maxOutputTokens' => 4096],
]); // $model->providerName と $model->modelId

$removed = $config->removeModel([
    'providerName' => 'my-provider',
    'modelId' => 'my-model',
]); // $removed->providerRemoved
```

`read()`は保存順の設定を返しますが、認証情報、ヘッダー、コマンドなどの秘密情報は返しません。各プロバイダーの`credentialStatus`は`present`、`absent`、`unavailable`のいずれかです。`updateProvider()`は共通設定を差し替え、省略された認証情報、カスタムヘッダー、ローカルモデル設定を維持します。`updateModel()`は指定されたプロバイダー名とモデルIDのモデル定義を差し替えます。`removeModel()`が最後のモデルを削除した場合はプロバイダーと管理対象の認証情報も削除され、`providerRemoved`が`true`になります。各更新・削除には対応する`ProvidersConfigUpdateProviderRequest`、`ProvidersConfigUpdateModelRequest`、`ProvidersConfigRemoveModelRequest`も使用できます。
