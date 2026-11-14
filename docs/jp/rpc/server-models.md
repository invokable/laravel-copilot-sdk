# モデル・ツール・アカウント

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
Copilot::client()->rpc()->ping();

// models
Copilot::client()->rpc()->models()->list();
// セッションごとのGitHubトークンで取得
Copilot::client()->rpc()->models()->list(new ModelsListRequest(gitHubToken: $token));
// モデルのトークン価格の単位を取得。models.listと同じアカウント選択情報を渡す
$priceUnits = Copilot::client()->rpc()->models()->listTokenPriceUnits(
    new ModelsListTokenPriceUnitsRequest(gitHubToken: $token),
);
// $priceUnits->units は modelId と TokenPriceUnit|string の unit を持つ
foreach ($priceUnits->units as $priceUnit) {
    if ($priceUnit->unit === TokenPriceUnit::NanoAiu) {
        // nano-AIU を AI Credits に換算する場合は 1,000,000,000 で割る
    }
}
// 組み込みモデルカタログ
Copilot::client()->rpc()->models()->getBuiltInCatalog();

// tools
Copilot::client()->rpc()->tools()->list();

// account
Copilot::client()->rpc()->account()->getQuota();
// セッションごとのGitHubトークンで取得
Copilot::client()->rpc()->account()->getQuota(new AccountGetQuotaRequest(gitHubToken: $token));

// 設定されたGitHubホスト名から絶対URLを解決（認証不要）
$hostUrl = Copilot::client()->rpc()->account()->getHostUrl([
    'copilotGhHost' => 'github.example.com',
])->url;

// 認証済みの資格情報を含まないidentityをCopilot Freeに登録
$enrollment = Copilot::client()->rpc()->account()->subscribeCopilotFree([
    'identity' => [
        'type' => 'token',
        'host' => 'https://github.com',
        'login' => 'octocat',
    ],
]);
// $enrollment->kind / $enrollment->status / $enrollment->message
```
