# Feature Flags・Workflow

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## セッション Feature Flags（experimental）

`$session->rpc()->featureFlags()`からセッションに紐づくFeature Flagサービスを設定・読み取り・更新できます。`configure()`と`update()`は現在のサービス状態を、`read()`はスナップショットと指定したフラグの評価結果を型付きDTOで返します。各呼び出しにはセッションIDが自動で追加されます。

```php
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsConfigureRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsReadRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsUpdateRequest;

$featureFlags = $session->rpc()->featureFlags();

$snapshot = $featureFlags->configure(new SessionFeatureFlagsConfigureRequest(
    isStaff: false,
    isExperimental: false,
    isTeam: false,
    online: true,
    streamerMode: false,
));

$read = $featureFlags->read(new SessionFeatureFlagsReadRequest(
    flags: ['example-feature'],
));

$updated = $featureFlags->update(new SessionFeatureFlagsUpdateRequest(
    secondaryAssignmentContext: 'workspace',
));
```

## workflow

Copilot CLI 1.0.89 系で追加されたワークフロー、コネクター、診断などの実験的RPC APIも、`SessionRpc`から利用できます。これらの可変プロトコルpayloadはJSON互換の配列で渡し、レスポンスも配列として返します。各呼び出しには現在の`sessionId`が自動で追加されます。

```php
// CLI側に登録されたワークフローを実行し、実行状況を確認
$run = $session->rpc()->workflow()->run([
    'name' => 'weekly-summary',
    'args' => ['team' => 'platform'],
]);
$current = $session->rpc()->workflow()->getRun(['runId' => $run['runId']]);

// 診断情報とコネクター一覧
$diagnostics = $session->rpc()->diagnostics()->read(['sources' => ['mcp']]);
$connectors = $session->rpc()->connectors()->list();
$account = $session->rpc()->connectors()->getAccount(); // 選択中アカウントがあればConnectorSessionAccount

// プラグインマーケットプレイス
$marketplaces = $session->rpc()->plugins()->marketplaces()->list();

// アクティブセッションを終了し、sessionEnd hookをバックグラウンドで処理する
$session->rpc()->shutdown(['detachSessionEndHooks' => true]);
```

`workflow()`はCLIが提供する低レベルRPCを呼び出します。Node.js SDKのようにPHPクロージャーをワークフローとして登録・実行する高レベルAPIは、この同期では追加していません。これらの新しいRPCグループはCLIのバージョンに依存し、実験的であるため、対応するCLIでのみ利用してください。
