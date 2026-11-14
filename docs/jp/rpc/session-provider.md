# プロバイダー（BYOK）

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
// provider: セッションが使用しているプロバイダーのエンドポイント情報を取得 (experimental)
// BYOK（Bring Your Own Key）構成で直接LLMバックエンドを呼び出す際に使用
use Revolution\Copilot\Types\Rpc\ProviderGetEndpointRequest;

$endpoint = $session->rpc()->provider()->getEndpoint();
// $endpoint->baseUrl - LLMクライアントライブラリに渡すベースURL
// $endpoint->type - プロバイダー種別（openai, anthropic など）
// $endpoint->headers - リクエストに含めるHTTPヘッダー
// $endpoint->apiKey - 認証情報（nullable）
// $endpoint->sessionToken - 短命のセッション認証情報（nullable）
// $endpoint->wireApi - プロバイダー固有のワイヤーAPI（nullable）

// 選択中モデルのプロバイダー構成状態を取得（資格情報の検証やプロバイダーへの通信は行わない） (experimental)
$status = $session->rpc()->provider()->getStatus();
// $status->hasCustomProvider - GitHub認証の代わりにカスタムプロバイダーを使っているか

// 特定モデルのエンドポイントを取得
$endpoint = $session->rpc()->provider()->getEndpoint(
    new ProviderGetEndpointRequest(model: 'gpt-5')
);

// provider: セッション内のプロバイダー接続設定を更新 (experimental)
$session->rpc()->provider()->updateProvider([
    'provider' => ['name' => 'example', 'baseUrl' => 'https://example.com/v1'],
]);

// provider: セッション内の1モデルをproviderとidで指定して更新 (experimental)
$session->rpc()->provider()->updateModel([
    'model' => ['provider' => 'example', 'id' => 'model', 'maxOutputTokens' => 4096],
]);
// どちらも保存済み設定や他のモデルを変更しません。
// updateProviderは既存の認証情報を維持し、認証情報やカスタムヘッダーは受け付けません。
// 配列の代わりにSessionProviderUpdateProviderRequest / SessionProviderUpdateModelRequestも使用できます。

// provider: ホスト管理モデルをBYOKレジストリから取り下げる (experimental)
$result = $session->rpc()->provider()->withdraw(['models' => ['provider/model']]);
// $result->withdrawn, $result->providersRemoved, $result->modelDeselected
```
