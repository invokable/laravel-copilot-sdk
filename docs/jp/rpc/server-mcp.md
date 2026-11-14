# MCP・コネクター

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
use Revolution\Copilot\Enums\McpInstallationSecretStorage;
use Revolution\Copilot\Enums\OptionsUpdateToolFilterPrecedence;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\McpApplyInstallRequest;
use Revolution\Copilot\Types\Rpc\McpApplyUninstallRequest;
use Revolution\Copilot\Types\Rpc\McpInstallationOperationRequest;
use Revolution\Copilot\Types\Rpc\McpInstallationsRequest;
use Revolution\Copilot\Types\Rpc\McpPlanUninstallRequest;
use Revolution\Copilot\Types\Rpc\McpPrepareInstallRequest;
use Revolution\Copilot\Types\Rpc\McpRegistryCancelRequest;
use Revolution\Copilot\Types\Rpc\McpRegistrySearchRequest;
use Revolution\Copilot\Types\Rpc\McpServerCardReference;
use Revolution\Copilot\Types\Rpc\McpShouldExcludeGitHubToolsRequest;

// mcp config (MCPサーバー設定の管理)
Copilot::client()->rpc()->mcp()->list();
Copilot::client()->rpc()->mcp()->add(new McpConfigAddRequest(
    name: 'my-server',
    config: new McpServerValue(type: 'local', command: 'php', args: ['artisan', 'mcp']),
));
// args は省略可能
Copilot::client()->rpc()->mcp()->update(new McpConfigUpdateRequest(
    name: 'my-server',
    config: new McpServerValue(type: 'http', url: 'https://mcp.example.com'),
));
Copilot::client()->rpc()->mcp()->remove(new McpConfigRemoveRequest(name: 'my-server'));
// MCPサーバーを有効化/無効化（グローバル設定）
Copilot::client()->rpc()->mcp()->enable(new McpConfigEnableRequest(names: ['my-server']));
Copilot::client()->rpc()->mcp()->disable(new McpConfigDisableRequest(names: ['my-server']));
// インメモリキャッシュをクリア（次回読み込み時にディスクから再取得）
Copilot::client()->rpc()->mcp()->reload();

// MCPレジストリ検索 (experimental/internal)
// $authInfo はホスト側で用意したJSON認証情報です。SDKはその内容を解釈しません。

$registry = Copilot::client()->rpc()->mcp()->registry();
$requestId = $registry->allocateRequestId()->requestId;
$servers = $registry->search(new McpRegistrySearchRequest(
    requestId: $requestId,
    authInfo: $authInfo, // ホストから渡す認証情報。SDKでは内容を解釈しません。
    query: 'database',
    limit: 20,
));
// $servers->servers はレジストリのサーバーデータ（形を解釈せず保持）
// 未使用の予約IDは解放できます。処理中の検索は別の処理からcancel()を呼び出してキャンセルします。
$unusedRequestId = $registry->allocateRequestId()->requestId;
$cancelResult = $registry->cancel(new McpRegistryCancelRequest(requestId: $unusedRequestId));
// 未使用IDの解放ではcanceledはfalse。実行中の検索を停止した場合のみtrue。

// MCPカタログ経由のインストール / アンインストール (experimental)
// 結果は kind を持つ判別ユニオンの連想配列です。kind を確認してから各フィールドを参照してください。

$mcp = Copilot::client()->rpc()->mcp();
$contract = new CatalogClientContract(
    protocolVersion: 1,
    requiredCapabilities: ['mcp-server-card', 'mcp-install-planning'],
);

// $candidateHandle、$searchId、$cardReferenceはカタログ検索結果から取得します。
// $sessionIdはこのClientに既に接続されているセッションIDです。
$planned = $mcp->planInstall([
    'contract' => $contract->toArray(),
    'source' => ['kind' => 'candidate', 'candidateHandle' => $candidateHandle, 'searchId' => $searchId],
]);

if (($planned['kind'] ?? null) === 'planned') {
    $plan = $planned['plan'];
    $prepared = $mcp->prepareInstall(new McpPrepareInstallRequest(
        contract: $contract,
        planHandle: $plan['planHandle'],
        choiceId: $plan['transportChoices'][0]['choiceId'],
        inputs: [],
        policySessionId: $sessionId,
        secrets: [],
        secretStorage: McpInstallationSecretStorage::Keychain,
        source: McpServerCardReference::fromArray($cardReference),
    ));

    // applyInstall中に、クライアント作成時に設定したinstallation_confirmation_handlerが呼び出されます。
    if (($prepared['kind'] ?? null) === 'outcome'
        && ($prepared['outcome']['kind'] ?? null) === 'install-prepared') {
        $applied = $mcp->applyInstall(new McpApplyInstallRequest(
            contract: $contract,
            operationId: $prepared['outcome']['operation']['operationId'],
            policySessionId: $sessionId,
        ));
    }
}

// 所有するインストールの一覧と未完了操作の復旧
$installations = $mcp->installations();
$installations->list(new McpInstallationsRequest(contract: $contract, policySessionId: $sessionId));
$installations->recover(new McpInstallationsRequest(contract: $contract, policySessionId: $sessionId));
$installations->status(new McpInstallationOperationRequest($contract, $operationId));
$installations->cancel(new McpInstallationOperationRequest($contract, $operationId));

// アンインストールは所有権のあるinstallationIdを指定して計画し、返されたplanHandleを適用します。
$uninstallPlan = $mcp->planUninstall(new McpPlanUninstallRequest(
    contract: $contract,
    installationId: $installationId,
    policySessionId: $sessionId,
));
if (($uninstallPlan['kind'] ?? null) === 'outcome'
    && ($uninstallPlan['outcome']['kind'] ?? null) === 'uninstall-planned') {
    $mcp->applyUninstall(new McpApplyUninstallRequest(
        contract: $contract,
        planHandle: $uninstallPlan['outcome']['plan']['planHandle'],
        policySessionId: $sessionId,
    ));
}

// GitHub MCPツールを除外できるか判定 (experimental/internal)

$exclude = Copilot::client()->rpc()->mcp()->shouldExcludeGitHubTools(
    new McpShouldExcludeGitHubToolsRequest(
        availableTools: ['builtin:bash'],
        excludedTools: [],
        toolFilterPrecedence: OptionsUpdateToolFilterPrecedence::Available,
    ),
)->excludeGhReplaceableTools;

// mcp discover (MCPサーバーの自動検出)
Copilot::client()->rpc()->mcp()->discover(new McpDiscoverRequest(
    workingDirectory: '/path/to/project',
));
// 引数なしで実行可能
$result = Copilot::client()->rpc()->mcp()->discover();
// $result->servers は DiscoveredMcpServer の配列
```
