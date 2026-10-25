# RPC(Remote Procedure Call)

公式SDKでは最近の新機能は`api.schema.json`を元にした自動コード生成で対応しています。Laravel版では生成後の他言語版を参考に同じように実装しています。

## ServerRpc

Clientに紐づくRPCクラス。

### 接続・モデル・ツール・アカウント

`ping()`と`connect()`で接続を確認・開始し、`registerExtensionLaunchProvider()`で拡張機能の起動プロバイダーを登録します。モデル一覧・価格単位・組み込みカタログは`models()`、利用可能なツール一覧は`tools()`から取得できます。`account()`ではクォータ、認証状態、ログインユーザーの一覧、ログイン・ログアウトを扱い、`accounts()`ではMicrosoft Entra IDのトークン取得を行います。

### MCP・コネクター・Secrets

`mcp()`はMCP設定の一覧・追加・更新・削除、有効化、検出、レジストリ検索、インストール計画と適用を提供します。`connectors()`ではコネクター機能とアカウント、カタログを確認できます。サーバー側でのコネクター操作はアカウント単位の一覧取得・更新が中心です。実験的な`secrets()->addFilterValues()`は、ホストが生成した動的シークレットをCLIのredaction filterに登録し、session logsやexportsでマスクします。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesRequest;

$result = Copilot::client()->rpc()->secrets()->addFilterValues(
    new SecretsAddFilterValuesRequest(values: [$accessToken]),
);
// $result->ok

// 配列でも指定できます
Copilot::client()->rpc()->secrets()->addFilterValues([
    'values' => [$accessToken],
]);
```

このRPCはOAuthトークンを取得するものではなく、値はSDK内で永続化・追加ログ出力されません。ホスト側で取得した値を明示的に登録してください。

### Skills・Plugins・Agents・Instructions・Extensions

各グループから探索や利用可能なパスの取得、設定変更、再読み込みを行えます。`commands()`は組み込みコマンド一覧、`hooks()`はセッション開始前のフック探索を提供します。SkillsとPluginsは有効化・無効化やインストール管理に対応し、Agentsは組み込みエージェント情報も取得できます。Extensionsは永続化された拡張機能を探索・有効化・無効化します。

### カタログとインストール

`catalog()`で候補検索・選択・フィード取得を行い、Skills・Plugins・MCPの各APIでインストール計画の確認、適用、状態照会やキャンセルを行います。計画と適用は別操作です。実際のインストール例は後半の各項目を参照してください。

### セッション・ホスト・実行環境

`sessions()`は永続化済みセッションの検索、イベント読み取り、サイズ確認、削除・整理、forkを扱います。Workspaceの作成・読み込みやリモート制御も利用できます。`host()`はホストの起動、セッション公開・一覧・公開解除、認証、受付ポリシー変更、破棄を行います。AHPホストの高レベルAPIは[AHPホスト](./ahp.md)を参照してください。`runtime()`はランタイムのライフサイクルを扱います。

### Git・環境・サンドボックス・ポリシー

`git()`からブランチや作業ディレクトリ、Git remoteの情報を取得し、`gitHubRepository()`と`gitHubOwners()`からGitHubリポジトリや所有者を探索できます。`environments()`はMission Control環境、`sandbox()`と`sandboxProxyCa()`はサンドボックス対応状況やProxy CA、`contentExclusion()`は除外ポリシーの検証、`managedSettings()`と`userSettings()`は設定を扱います。

### ローカルモデル・HTTP推論・ファイルシステム

`azureMl()`はローカルモデルのカタログ・解決・ダウンロードと、その状態確認・キャンセル・削除を提供します。`llmInference()`はモデル層のHTTPリクエストをクライアント側へ中継する実験的APIです。`sessionFs()`はセッションのファイルシステム／SQLiteコールバックプロバイダーを登録します。

### 基本的な呼び出し例

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Enums\TokenPriceUnit;
use Revolution\Copilot\Types\Rpc\ModelsListTokenPriceUnitsRequest;
use Revolution\Copilot\Types\Rpc\ModelList;

// モデルリストの取得
// 返り値はModelList
$result = Copilot::client()->rpc()->models()->list();
// modelsはModelInfoの配列
$models = $result->models;

// 初期からあるlistModels()とほとんど同じ
// 返り値はModelInfoの配列
Copilot::client()->listModels();
```

元からSDKに含まれてた機能も自動コード生成版で再度追加されています。

### 機能別の使用例

以下に、上記の機能の代表的な呼び出し例をまとめています。

```php
use Illuminate\Http\Client\Response;
use Revolution\Copilot\Enums\CatalogCandidateKind;
use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\CatalogSelectionDecision;
use Revolution\Copilot\Enums\CatalogTrustTier;
use Revolution\Copilot\Enums\EventsReadDirection;
use Revolution\Copilot\Enums\McpInstallationSecretStorage;
use Revolution\Copilot\Enums\OptionsUpdateToolFilterPrecedence;
use Revolution\Copilot\Enums\SkillInstallationManagementOutcomeKind;
use Revolution\Copilot\Enums\SkillInstallationManagementResultKind;
use Revolution\Copilot\Enums\AzureMlRegistry;
use Revolution\Copilot\Types\Rpc\AzureMlAcquireFamilyRequest;
use Revolution\Copilot\Types\Rpc\AzureMlCancelRequest;
use Revolution\Copilot\Types\Rpc\AzureMlCatalogRequest;
use Revolution\Copilot\Types\Rpc\AzureMlDiscardRequest;
use Revolution\Copilot\Types\Rpc\AzureMlDownloadRequest;
use Revolution\Copilot\Types\Rpc\AzureMlLatestRequest;
use Revolution\Copilot\Types\Rpc\AzureMlReleaseFamilyRequest;
use Revolution\Copilot\Types\Rpc\AzureMlResolveRequest;
use Revolution\Copilot\Types\Rpc\AzureMlStatusRequest;
use Revolution\Copilot\Types\Rpc\AgentsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogGetGeneratedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogGetSavedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogMaterializationReference;
use Revolution\Copilot\Types\Rpc\CatalogSearchRequest;
use Revolution\Copilot\Types\Rpc\CatalogSelectionRequest;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProfile;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveRequest;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveResult;
use Revolution\Copilot\Types\Rpc\HooksDiscoverRequest;
use Revolution\Copilot\Types\Rpc\InstructionsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\InstructionsGetDiscoveryPathsRequest;
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
use Revolution\Copilot\Types\Rpc\PluginsBuiltinSetRequest;
use Revolution\Copilot\Types\Rpc\PluginsDisableRequest;
use Revolution\Copilot\Types\Rpc\PluginsEnableRequest;
use Revolution\Copilot\Types\Rpc\PluginsInstallRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesAddRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesBrowseRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesRefreshRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesRemoveRequest;
use Revolution\Copilot\Types\Rpc\PluginsUninstallRequest;
use Revolution\Copilot\Types\Rpc\PluginsUpdateRequest;
use Revolution\Copilot\Types\Rpc\SessionsListRequest;
use Revolution\Copilot\Types\Rpc\SessionsPruneOldRequest;
use Revolution\Copilot\Types\Rpc\SessionsReadPersistedEventsRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenCreateRequest;
use Revolution\Copilot\Enums\SessionsOpenStatus;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\Rpc\SkillApplyInstallRequest;
use Revolution\Copilot\Types\Rpc\SkillApplyUninstallRequest;
use Revolution\Copilot\Types\Rpc\SkillInstallationOperationRequest;
use Revolution\Copilot\Types\Rpc\SkillInstallationsRequest;
use Revolution\Copilot\Types\Rpc\SkillPlanInstallRequest;
use Revolution\Copilot\Types\Rpc\SkillPlanInstallFromReferenceRequest;
use Revolution\Copilot\Types\Rpc\SkillPlanUninstallRequest;
use Revolution\Copilot\Types\Rpc\SkillSetEnabledRequest;
use Revolution\Copilot\Types\Rpc\SkillsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\UIEphemeralQueryRequest;
use Revolution\Copilot\Types\SessionListFilter;
use Revolution\Copilot\Support\CopilotRequestContext;
use Revolution\Copilot\Support\CopilotRequestHandler;

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

// Azure ML ローカルモデル管理（experimental）
$azureMl = Copilot::client()->rpc()->azureMl();
$catalog = $azureMl->catalog(new AzureMlCatalogRequest(includeDev: false));
$latest = $azureMl->latest(new AzureMlLatestRequest(
    registry: AzureMlRegistry::AzureMl,
    name: 'phi',
));
$resolved = $azureMl->resolve(new AzureMlResolveRequest(
    registry: AzureMlRegistry::AzureMl,
    name: $latest->name,
    version: $latest->version,
));

// requestId と leaseId はこの SDK 接続に紐づくため、別の Client では再利用しない
$lease = $azureMl->acquireFamily(new AzureMlAcquireFamilyRequest(
    registry: AzureMlRegistry::AzureMl,
    name: $latest->name,
));

try {
    $download = $azureMl->download(new AzureMlDownloadRequest(
        requestId: 'download-1',
        registry: AzureMlRegistry::AzureMl,
        name: $latest->name,
        version: $latest->version,
        destination: 'models/phi/'.$latest->version,
        leaseId: $lease->leaseId,
    ));
} finally {
    $azureMl->releaseFamily(new AzureMlReleaseFamilyRequest(
        leaseId: $lease->leaseId,
        registry: AzureMlRegistry::AzureMl,
        name: $latest->name,
    ));
}

// download() は完了まで待機する。処理中の進捗確認・キャンセルは、
// 同じ接続の $azureMl を使う別 Fiber などから実行する
// $status = $azureMl->status(new AzureMlStatusRequest(requestId: 'download-1'));
// $cancelled = $azureMl->cancel(new AzureMlCancelRequest(requestId: 'download-1'));
// cancel() の bool はキャンセル要求の受理を示し、クリーンアップ完了は download() で確認する

// 非アクティブなバージョンを削除する
$azureMl->discard(new AzureMlDiscardRequest(destination: 'models/phi/previous-version'));

// resolve() と download() の型には署名付き URL の専用フィールドはない
// download() は既定でタイムアウトなし。秒数を指定する場合は第2引数に渡す

// 新規セッションで利用可能な組み込みスラッシュコマンド一覧（experimental）
Copilot::client()->rpc()->commands()->list();

// セッション開始前のフック探索（experimental）
Copilot::client()->rpc()->hooks()->discover(new HooksDiscoverRequest(
    projectPaths: ['/path/to/project'],
));

// ホストのサンドボックス対応状況（experimental）
$hostSupport = Copilot::client()->rpc()->sandbox()->getHostSupport();
// $hostSupport->supported, $hostSupport->reason, $hostSupport->capabilities

// tools
Copilot::client()->rpc()->tools()->list();

// account
Copilot::client()->rpc()->account()->getQuota();
// セッションごとのGitHubトークンで取得
Copilot::client()->rpc()->account()->getQuota(new AccountGetQuotaRequest(gitHubToken: $token));

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

// user settings (ユーザー設定)
// 現行CLIにuser.settings.reload RPCはありません。get()の対象はsettings.jsonです。
Copilot::client()->rpc()->userSettings()->set(['settings' => ['theme' => 'dark']]); // 戻り値なし

// mcp discover (MCPサーバーの自動検出)
Copilot::client()->rpc()->mcp()->discover(new McpDiscoverRequest(
    workingDirectory: '/path/to/project',
));
// 引数なしで実行可能
$result = Copilot::client()->rpc()->mcp()->discover();
// $result->servers は DiscoveredMcpServer の配列

// llmInference（experimental: モデル層へのHTTPリクエストをフック）
$client = Copilot::client([
    'request_handler' => new class extends CopilotRequestHandler
    {
        public function sendRequest(CopilotRequestContext $context, string $method, string $body): Response
        {
            $context->url = 'https://proxy.example.com/v1/chat/completions';
            $context->headers['X-Request-Source'] = ['laravel'];

            return parent::sendRequest($context, $method, $body);
        }
    },
]);

// 起動時にllmInference.setProviderが送信され、httpRequestStart / httpRequestChunkを受け取ります。
// sendRequestを上書きしないハンドラはLaravel HTTP ClientでHTTP/SSEを透過転送します。
// Closureを指定する場合はfn (CopilotRequestContext $context, string $method, string $body): Responseの形式でResponseを返します。
// request_handlerはexperimentalです。WebSocket transportは未対応で、501相当のエラー応答になります。

// sessionFs (セッションファイルシステムプロバイダーの登録)
Copilot::client()->rpc()->sessionFs()->setProvider(new SessionFsSetProviderRequest(
    initialCwd: '/path/to/project',
    sessionStatePath: '.copilot/sessions',
    conventions: 'posix',
));

// 拡張機能の起動方法を解決するクライアントオプション（応答は15秒以内）
$client = Copilot::client([
    'extensionLaunchProvider' => fn (ExtensionLaunchProviderResolveRequest $request) => new ExtensionLaunchProviderResolveResult(
        launch: new ExtensionLaunchProfile(
            executable: '/usr/bin/node',
            args: [$request->modulePath],
        ),
    ),
]);
// 対応する起動方法がない場合はnullを返します。
// fn (ExtensionLaunchProviderResolveRequest $request) => null

$sessions = Copilot::client()->rpc()->sessions();

// sessions (experimental: 永続化済みセッションの照会・管理)

// sessions.open は kind で作成・再開・resumeLast・attach・remote/cloud 接続・handoff を選択します。
$opened = $sessions->open(new SessionsOpenCreateRequest(
    options: new SessionConfig(sessionId: 'new-session', model: 'gpt-5'),
));
if ($opened->status === SessionsOpenStatus::Created) {
    echo $opened->sessionId;
}
// 配列でも指定できます。再開時の sessionId は variant のトップレベルに指定します。
$resumed = $sessions->open(['kind' => 'resume', 'sessionId' => 'existing-session']);
// 戻り値は SessionsOpenResult。not_found の場合は sessionId がありません。
// sessionApi、onTaskCreated、onProgress、onConfirm は in-process 専用で、wire payload には含まれません。

$listed = $sessions->list(new SessionsListRequest(
    source: 'local',
    metadataLimit: 50,
    filter: new SessionListFilter(cwd: '/path/to/project'),
));
// 各 SessionMetadata の isRemote で local / remote セッションを判別
foreach ($listed->sessions as $session) {
    echo $session->sessionId;
}

$sessions->findByTaskId(['taskId' => 'task-id']);
$sessions->findByPrefix(['prefix' => 'session-id-prefix']);
$sessions->getLastForContext(['context' => ['cwd' => '/path/to/project']]);
$sizes = $sessions->getSizes(); // $sizes->sizes は session ID => byte 数
$inUse = $sessions->checkInUse(['sessionIds' => ['session-id']]);
$deleted = $sessions->bulkDelete(['sessionIds' => ['session-id']]);

// アクティブなセッションを閉じる（履歴は削除しない）
$sessions->close(['sessionId' => 'session-id']);

// 保留中イベントをディスクへ保存
$sessions->save(['sessionId' => 'session-id']);

// このプロセスが保持するセッションのin-use lockを明示的に解放
$sessions->releaseLock(['sessionId' => 'session-id']);

// reloadPluginHooksはuser/plugin hooksを再読み込みする。
// folder trustの確認前はrepo hooksを遅延させ、信頼確認後に続けてロードする。
$sessions->reloadPluginHooks([
    'sessionId' => 'session-id',
    'deferRepoHooks' => true,
]);
$loadedHooks = $sessions->loadDeferredRepoHooks(['sessionId' => 'session-id']);
// $loadedHooks->startupPrompts と $loadedHooks->hookCount

// Manager-wideの追加plugin設定を置換（空配列でクリア）
$sessions->setAdditionalPlugins(['plugins' => []]);

// local session metadata の summary / context を補完
$enriched = $sessions->enrichMetadata([
    'sessions' => [[
        'sessionId' => 'session-id',
        'startTime' => '2026-10-17T09:00:00Z',
        'modifiedTime' => '2026-10-17T09:00:00Z',
        'isRemote' => false,
    ]],
]);

// close() はshutdown・イベント保存・ロック解放を行ってactive sessionを破棄しますが、
// persisted historyは削除しません。$session->disconnect() はsession.detachでこのSDKとの
// 接続とPHP側のin-memory resourcesを解放します。永続データの削除にはbulkDelete()を使います。

// セッションを開かず、cursor を使って永続化済みイベントをページング
$cursor = null;
do {
    $page = $sessions->readPersistedEvents(new SessionsReadPersistedEventsRequest(
        sessionId: 'session-id',
        cursor: $cursor,
        max: 100,
        direction: EventsReadDirection::FORWARD,
    ));

    foreach ($page->events as $event) {
        // イベントを処理
    }

    $cursor = $page->cursor;
} while ($page->hasMore);

// dryRun は削除せず候補と見積もりを返す。実削除する場合は dryRun: false を指定
$preview = $sessions->pruneOld(new SessionsPruneOldRequest(
    olderThanDays: 30,
    dryRun: true,
));

// sessions.fork
$sessions->fork(new SessionsForkRequest(
    sessionId: 'source-session-id',
    toEventId: 'evt-boundary', // オプション: この ID より前のイベントのみ含める
));

// skills (サーバーレベルのスキル管理)
// スキルの探索
$result = Copilot::client()->rpc()->skills()->discover();
// $result->skills は ServerSkill の配列
// オプションでプロジェクトパスを指定
$result = Copilot::client()->rpc()->skills()->discover(new SkillsDiscoverRequest(
    projectPaths: ['/path/to/project'],
    skillDirectories: ['/custom/skills'],
));

// 無効化するスキルの設定
Copilot::client()->rpc()->skills()->config()->setDisabledSkills(
    new SkillsConfigSetDisabledSkillsRequest(disabledSkills: ['skill-name'])
);

// 個別のスキルを有効化・無効化
Copilot::client()->rpc()->skills()->config()->setSkillDisabled(
    new SkillsConfigSetSkillDisabledRequest(name: 'skill-name', disabled: true)
);

// catalog.search → catalog.select でスキル候補を選択 (experimental)

$contract = new CatalogClientContract(1, ['catalog-selection', 'ai-skill-installation']);
$catalog = Copilot::client()->rpc()->catalog();
$search = $catalog->search(new CatalogSearchRequest(
    contract: $contract,
    query: 'code review skill',
    kinds: [CatalogCandidateKind::AiSkill],
));
$selection = $catalog->select(new CatalogSelectionRequest(
    contract: $contract,
    sessionId: $sessionId,
    selectionRef: $search['candidates'][0]['selectionRef'],
    outcome: CatalogSelectionDecision::Selected,
));

// category filterにはcatalog-search-categoriesを要求する
$categoryContract = new CatalogClientContract(3, [
    CatalogCapability::CatalogSearchCategories->value,
]);
$categorySearch = $catalog->search(new CatalogSearchRequest(
    contract: $categoryContract,
    query: 'design tools',
    categories: ['design', 'test'],
));
// policySessionIdを指定する場合は、contractでcredential-requiredとsession-boundも要求し、
// 既存のattached sessionだけにバインドします。sessionを新規作成・再開することはありません。

// 保存済み・生成 catalog feed (credential required)
$feedContract = new CatalogClientContract(3, [
    CatalogCapability::CatalogFeeds->value,
    CatalogCapability::CatalogSearchCredentialRequired->value,
]);
$savedFeed = $catalog->getSavedFeed(new CatalogGetSavedFeedRequest(
    contract: $feedContract,
    feedId: 'opaque-feed-id',
    trustTiers: [CatalogTrustTier::T1],
));

// 既存の attached session にバインドする場合は session-bound capabilityも要求
$sessionFeedContract = new CatalogClientContract(3, [
    CatalogCapability::CatalogFeeds->value,
    CatalogCapability::CatalogSearchCredentialRequired->value,
    CatalogCapability::CatalogSearchSessionBound->value,
]);
$generatedFeed = $catalog->getGeneratedFeed(new CatalogGetGeneratedFeedRequest(
    contract: $sessionFeedContract,
    strategy: 'telemetry',
    key: 'popular',
    policySessionId: $sessionId,
    trustTiers: [CatalogTrustTier::T1, CatalogTrustTier::T2],
));
// entriesはfeed順を保持。trust tierに合わないものもfilteredとして位置を保ち、
// 表示できないentryはunavailableとreasonで返ります。
// feedがない場合はkind=unavailable、reason=feed-not-found。認証・交渉・ポリシー拒否も
// それぞれの型付き結果として返り、catalog feed RPCはインストールや設定を行いません。

// スキルのインストール計画と適用

$skills = Copilot::client()->rpc()->skills();
if ($selection['kind'] === 'selected') {
    $planned = $skills->planInstall(new SkillPlanInstallRequest(
        contract: $contract,
        candidateHandle: $selection['candidateHandle'],
        policySessionId: $sessionId,
    ));
    if ($planned->kind === SkillInstallationManagementResultKind::Outcome
        && $planned->outcome?->kind === SkillInstallationManagementOutcomeKind::InstallPlanned) {
        // applyInstall() は installations.confirm による確認後に適用する
        $result = $skills->applyInstall(new SkillApplyInstallRequest(
            contract: $contract,
            planHandle: $planned->outcome->plan['planHandle'],
            policySessionId: $sessionId,
        ));
    }
}

// Agent Finder の materialisation reference からスキルのインストールを計画
$referenceContract = new CatalogClientContract(3, [
    'skill-confirmed-installation',
    'skill-reference-installation',
]);
$referencePlan = $skills->planInstallFromReference(new SkillPlanInstallFromReferenceRequest(
    contract: $referenceContract,
    reference: new CatalogMaterializationReference(
        resourceId: '12345',
        revisionId: '67890',
        schemaVersion: '1.0.0',
        descriptorDigest: 'sha256:aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        bundleDigest: 'sha256:bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
    ),
    policySessionId: $sessionId,
));
// 成功時は $referencePlan->kind === SkillInstallationManagementResultKind::Outcome、
// $referencePlan->outcome->kind === SkillInstallationManagementOutcomeKind::InstallPlanned。
// 計画自体は副作用を起こさず、固定のAgent Finder authorityからdescriptorを取得し、
// descriptorとbundleのdigestを照合する。内容を確認し、既存の確認フローを完了してからapplyInstall()する。
// invalid-reference、descriptor-not-found、descriptor-withdrawn、digest-mismatch、
// unsupported-resource は計画結果の拒否理由として返る。
// GitHubの拒否理由 source-rate-limited、no-credential、credential-rejected は
// SkillInstallationFailureReason で区別され、retryAfterSecondsはsource-rate-limitedの場合だけ返る。
// network-failureのmessageはURL、検索クエリ、ハンドル、secretを含まない安全な説明です。

// インストール済みスキルと進行中の操作
$installations = $skills->installations();
$owned = $installations->list(new SkillInstallationsRequest($contract, $sessionId));
$pending = $installations->recover(new SkillInstallationsRequest($contract, $sessionId));
$status = $installations->status(new SkillInstallationOperationRequest($contract, 'operation-id'));
$cancelled = $installations->cancel(new SkillInstallationOperationRequest($contract, 'operation-id'));
$enabled = $installations->setEnabled(new SkillSetEnabledRequest(
    contract: $contract,
    installationId: 'installation-id',
    enabled: true,
    policySessionId: $sessionId,
));

// アンインストールも計画を確認してから適用
$removalPlan = $skills->planUninstall(new SkillPlanUninstallRequest(
    contract: $contract,
    installationId: 'installation-id',
    policySessionId: $sessionId,
));
$removed = $skills->applyUninstall(new SkillApplyUninstallRequest(
    contract: $contract,
    planHandle: $removalPlan['planHandle'],
    policySessionId: $sessionId,
));

// skills: スキル作成可能ディレクトリを取得 (experimental)
// スキルを配置するとランタイムが認識するディレクトリの一覧

$result = Copilot::client()->rpc()->skills()->getDiscoveryPaths();
// $result->paths は SkillDiscoveryPath の配列
// $result->paths[0]->path - ディレクトリパス
// $result->paths[0]->scope - スコープ（user, project, plugin）
// $result->paths[0]->preferredForCreation - 新規作成時の推奨ディレクトリか
// オプションでスコープを絞り込み
$result = Copilot::client()->rpc()->skills()->getDiscoveryPaths(
    new SkillsGetDiscoveryPathsRequest(scope: 'project')
);

// plugins (サーバースコープのプラグイン管理)

$plugins = Copilot::client()->rpc()->plugins();
$result = $plugins->list(); // 返り値は PluginList
$installed = $plugins->install(new PluginsInstallRequest(source: 'eslint@official'));
$plugins->uninstall(new PluginsUninstallRequest(name: 'eslint@official'));
$plugins->update(new PluginsUpdateRequest(name: 'eslint@official'));
$plugins->updateAll();
$plugins->enable(new PluginsEnableRequest(names: ['eslint@official']));
$plugins->disable(new PluginsDisableRequest(names: ['eslint@official']));

// ビルトインプラグインのディレクトリ設定
$plugins->builtin()->set(new PluginsBuiltinSetRequest(paths: ['/opt/copilot/plugins']));

// 全セッションで使うディレクトリは、クライアント設定で指定すると
// Client::start() が最初のセッション作成前に自動登録する
// config/copilot.php: 'builtin_plugin_directories' => ['/opt/copilot/plugins']

// マーケットプレイス管理
$marketplaces = $plugins->marketplaces();
$marketplaces->list();
$marketplaces->add(new PluginsMarketplacesAddRequest(source: 'github/copilot-plugins'));
$marketplaces->remove(new PluginsMarketplacesRemoveRequest(name: 'official', force: true));
$marketplaces->browse(new PluginsMarketplacesBrowseRequest(name: 'official'));
$marketplaces->refresh(new PluginsMarketplacesRefreshRequest(name: 'official'));

// agents (experimental: カスタムエージェント管理)
// カスタムエージェントの探索

$result = Copilot::client()->rpc()->agents()->discover();
// $result->agents は AgentInfo の配列
$result = Copilot::client()->rpc()->agents()->discover(new AgentsDiscoverRequest(
    projectPaths: ['/path/to/project'],
    excludeHostAgents: true,
));

// エージェント作成可能ディレクトリを取得 (experimental)

$result = Copilot::client()->rpc()->agents()->getDiscoveryPaths();
// $result->paths は AgentDiscoveryPath の配列
// $result->paths[0]->path - ディレクトリパス
// $result->paths[0]->scope - スコープ（user, project, plugin）
// $result->paths[0]->preferredForCreation - 新規作成時の推奨ディレクトリか
$result = Copilot::client()->rpc()->agents()->getDiscoveryPaths(
    new AgentsGetDiscoveryPathsRequest(scope: 'user')
);

// instructions (experimental: インストラクション管理)
// インストラクションの探索

$result = Copilot::client()->rpc()->instructions()->discover();
// $result->sources は InstructionSource の配列
$result = Copilot::client()->rpc()->instructions()->discover(
    new InstructionsDiscoverRequest(projectPaths: ['/path/to/project'])
);

// インストラクション作成可能ファイル/ディレクトリを取得 (experimental)

$result = Copilot::client()->rpc()->instructions()->getDiscoveryPaths();
// $result->paths は InstructionDiscoveryPath の配列
// $result->paths[0]->path - ファイル/ディレクトリパス
// $result->paths[0]->location - 場所（user, repository, working-directory, plugin）
// $result->paths[0]->kind - 種別（file, directory）
// $result->paths[0]->preferredForCreation - 新規作成時の推奨か
$result = Copilot::client()->rpc()->instructions()->getDiscoveryPaths(
    new InstructionsGetDiscoveryPathsRequest(location: 'repository')
);

// extensions (experimental: 永続化された拡張機能の管理)
$result = Copilot::client()->rpc()->extensions()->discover();
Copilot::client()->rpc()->extensions()->enable(['ids' => ['user:demo']]);
Copilot::client()->rpc()->extensions()->disable(['ids' => ['plugin:demo']]);

// environments (experimental: GitHub Mission Control 環境の検索・取得・削除)
$result = Copilot::client()->rpc()->environments()->list(['kind' => 'user-local']);
// $result->environments は GitHubEnvironment の配列
$env = Copilot::client()->rpc()->environments()->get(['environmentId' => 'env-id']);
Copilot::client()->rpc()->environments()->delete(['environmentId' => 'env-id']);
```

### Agent Plugin catalog候補

`catalog.search` で `agent-plugin-discovery` を要求し、検索対象の `kinds` に `plugin` を指定すると Agent Plugin 候補を取得できます。標準形式 `application/vnd.github.copilot-plugin` 以外の `application/vnd.anthropic.claude-plugin+json` と `application/agent-plugin+json` を受け取るには、`agent-plugin-discovery` に加えて `agent-plugin-formats` を要求してください。

`agent-plugin-confirmed-installation` はインストールの可否とレビュー用プランを返す capability で、これも `agent-plugin-discovery` と併せて要求します。許可すると候補に `installability` が含まれ、値は `installable`、`install-data-unavailable`、`feature-disabled`、`not-installable-kind` のいずれかです。`installPlan` は `installability` が `installable` の場合にだけ含まれます。プランは確認用のデータであり、この catalog RPC がインストールを実行することはありません。

プランの `sources` は優先順に `materialization`、`marketplace`、`repository` の候補を含みます。Marketplace には名前とリポジトリ、直接の Repository にはリポジトリと任意のパスが記録されます。materializationだけのプランでは `pluginName` が省略され、`components` は空です。名前や実際に導入される内容は `plugins.planInstall()` が返すレビューで確認してください。

選択済み候補は `plugins.planInstall()` で副作用のない検証付きプランを作成できます。`plugins.applyInstall()` は利用者に明示的な確認を求め、許可された場合にプランを適用します。適用後のプラグインは無効状態で登録されます。

レビューの `mcpServers` は検証済みファイルが宣言するサーバー名、transport、リモートの場合のhostを示します。これは安全性の保証ではないため、利用者が内容を確認してから明示的な確認を行ってください。インストール済みプラグインの `description` はmanifest由来です。`verifiedSource` はGitHubリポジトリと固定commitを示し、commitに固定された直接インストールでのみ存在します。`iconUrl` は外部の未信頼コンテンツなので、取得時に認証情報やGitHub資格情報を送らないでください。資格情報そのものは拒否理由として返されません。

インストール結果の `network-failure` では、`CatalogNetworkFailureReason`、HTTP `statusCode`、任意の `retryAfterSeconds` と安全に表示できる `message` を確認できます。

```php
use Revolution\Copilot\Enums\CatalogReferenceInstallationResultKind;
use Revolution\Copilot\Enums\PluginInstallationManagementOutcomeKind;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\PluginApplyInstallRequest;
use Revolution\Copilot\Types\Rpc\PluginPlanInstallRequest;

$contract = new CatalogClientContract(3, [
    'agent-plugin-confirmed-installation',
]);
$plugins = Copilot::client()->rpc()->plugins();

$planned = $plugins->planInstall(new PluginPlanInstallRequest(
    candidateHandle: $candidateHandle,
    contract: $contract,
    policySessionId: $sessionId,
));

if ($planned->kind === CatalogReferenceInstallationResultKind::Outcome
    && $planned->outcome?->kind === PluginInstallationManagementOutcomeKind::InstallPlanned) {
    $applied = $plugins->applyInstall(new PluginApplyInstallRequest(
        contract: $contract,
        planHandle: $planned->outcome->plan->planHandle,
        policySessionId: $sessionId,
    ));
}
```

Agent Finder の materialisation reference から計画する場合は `catalog.planInstallFromReference()` を使います。結果の `outcome->kind` は `plugin-install-planned`、`skill-install-planned`、`refused` のいずれかです。プラグインの計画は `plugins.applyInstall()` に渡し、スキルの計画は `skills.applyInstall()` に渡してください。

```php
use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\CatalogReferenceInstallationOutcomeKind;
use Revolution\Copilot\Enums\CatalogReferenceInstallationResultKind;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogPlanInstallFromReferenceRequest;
use Revolution\Copilot\Types\Rpc\PluginApplyInstallRequest;

$referenceContract = new CatalogClientContract(3, [
    CatalogCapability::CatalogReferenceInstallation->value,
    CatalogCapability::AgentPluginConfirmedInstallation->value,
]);
$planned = Copilot::client()->rpc()->catalog()->planInstallFromReference(
    new CatalogPlanInstallFromReferenceRequest(
        contract: $referenceContract,
        policySessionId: $sessionId,
        reference: $reference,
    ),
);

$plugins = Copilot::client()->rpc()->plugins();
if ($planned->kind === CatalogReferenceInstallationResultKind::Outcome
    && $planned->outcome?->kind === CatalogReferenceInstallationOutcomeKind::PluginInstallPlanned) {
    $plugins->applyInstall(new PluginApplyInstallRequest(
        contract: $referenceContract,
        planHandle: $planned->outcome->plan->planHandle,
        policySessionId: $sessionId,
    ));
}
```

### contentExclusion

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

### managedSettings (experimental: 管理設定)

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

### Runtime・Host のライフサイクル（experimental）

`host.unpublishSession()` は、所有する稼働中のホストから application publication を撤回します。公開を撤回しても、元のセッション履歴、worktree、実行中の作業は削除・停止されません。すでに公開されていない場合は `unpublished` が `false` になり、application 以外の publication はエラーになります。

```php
$result = Copilot::client()->rpc()->host()->unpublishSession([
    'hostId' => 'host-1',
    'sessionId' => 'session-1',
]);
// $result->unpublished
```

`host.dispose()` は `hostId` を指定してホストリスナーを破棄します。

```php
Copilot::client()->rpc()->host()->dispose(['hostId' => 'host-1']);
```

`host.setSessionCreationPolicy()` は、稼働中のホストでリモートの新規セッション受付を切り替えます。既存クライアントを切断せず、その後の受付だけに適用します。

```php
use Revolution\Copilot\Types\Rpc\HostSetSessionCreationPolicyRequest;

Copilot::client()->rpc()->host()->setSessionCreationPolicy(
    new HostSetSessionCreationPolicyRequest(
        hostId: 'host-1',
        allowCreatingNewSessions: false,
    ),
);
```

SDKが起動したstdioランタイムでは、`Client::stop()` がセッションを切断した後、JSON-RPC接続とプロセスを閉じる前に `runtime.shutdown` を呼び出します。ランタイムのクリーンアップ完了を最大10秒待ち、失敗時はエラーを戻り値に含めたうえで残りの停止処理を続けます。`cli_url` など外部ランタイムへの接続では、共有ランタイムを終了しないようこのRPCを呼び出しません。

CLIが対応するリクエストを特定できず`id: null`のJSON-RPCエラーを返した場合、実行中のリクエストはすべて、引用値を除去して長さを制限した診断メッセージを含む`JsonRpcException`で失敗します。接続は維持されますが、CLIがリクエストを処理済みの可能性もあるため、安全性を確認せずに自動再試行しないでください。CLIが読み取れない127階層を超える送信リクエストは送信前に拒否し、深すぎるハンドラー応答はJSON-RPCエラーに置き換えます。

## SessionRpc

Sessionに紐づくRPCクラス。

以前のSDKではできなかったプランモードの利用なども可能です。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\ModeSetRequest;
use Revolution\Copilot\Types\Rpc\PlanReadResult;

Copilot::start(function (CopilotSession $session) {
    $session->rpc()->mode()->set(new ModeSetRequest(mode: 'plan'));

    $response = $session->sendAndWait(prompt: '〇〇のプランを作成');

    $result = $session->rpc()->plan()->read();
    dump($result->content);

    $session->rpc()->mode()->set(new ModeSetRequest(mode: 'autopilot'));

    $response = $session->sendAndWait(prompt: 'プランに従って実装');
    dump($response->content());
});
```

### モデル・モード・名前・プラン

`model()`で現在のモデル確認や切り替え、reasoning effort・Auto tier・許可モデルの設定を行えます。`mode()`はモード、`name()`はセッション名、`plan()`はプラン本文とSQLite todoの読み書きを扱います。

### ワークスペース・インストラクション・LSP

`workspaces()`はファイルの一覧・読み取り・書き込み・移動・削除やディレクトリ操作を提供します。`instructions()`でインストラクションの取得・再読み込み・探索、`lsp()`で言語サーバー設定の初期化を行えます。

### Autopilot objective

`autopilotObjective()->getState()`はセッションの現在のAutopilot objective状態を型付きで返します。objectiveが存在しない場合、`state`は`null`です。Workspaceに保存されたobjectiveの有無を確認する`workspaces()->autopilotObjectiveExists()`とは異なるAPIです。

```php
$state = $session->rpc()->autopilotObjective()->getState()->state;

if ($state !== null) {
    dump($state->objective, $state->status, $state->turnCount, $state->creditLimit);
}
```

### エージェント・Fleet・バックグラウンドタスク

`agent()`でエージェントの一覧・選択・再読み込み、`fleet()`でFleetを開始します。`tasks()`ではバックグラウンドエージェントやクライアント所有タスクの登録、進捗取得・更新、移行、メッセージ送信、キャンセル、削除を扱います。

### MCP・Plugins・Extensions・Workflow

`mcp()`はサーバーの一覧・起動・停止・再読み込み、ツール・プロンプト・リソース、OAuth、sampling、動的ヘッダーの更新を扱います。`plugins()`と`extensions()`はセッション内の状態を管理し、`customizations()`はカスタマイズの再読み込みを行います。`workflow()`でワークフローを実行・照会し、`diagnostics()`と`connectors()`で診断情報やコネクターを取得できます。

### Tools・Permissions・Commands・UI

`tools()`と`permissions()`はイベントで届いた保留中リクエストへの応答や設定を扱います。`commands()`はコマンド一覧・実行・イベント応答、`ui()`はユーザー入力・elicitation・プラン承認などの要求への応答を提供します。`completions()`は入力候補を取得します。

### Accounts・認証・Providers

`accounts()`は`enumerate()`によるアカウント／プロバイダー／ログインアカウントの列挙、`get()`・`set()`、`begin()`・`advance()`・`cancel()`による対話型ログインを扱います。`gitHubAuth()`はセッションの認証状態と資格情報、`provider()`と`providers()`はモデル接続先やプロバイダーアダプター、`connectors()`はセッションのコネクター接続を扱います。

### 履歴・キュー・ログ・使用量

`history()`は会話の圧縮・中断・引き継ぎ要約・切り詰め、`queue()`は保留項目の挿入・移動・編集・削除・即時送信を提供します。`log()`、`eventLog()`、`usage()`、`quota()`、`limitPrediction()`でログやイベント、使用量と上限情報を扱います。`schedule()`ではスケジュール済みプロンプトを確認・停止できます。

### ライフサイクル・リモート・ホスト連携

`sendMessages()`は複数メッセージを1ターンで実行し、`suspend()`・`shutdown()`でセッションを停止します。`interruptMainTurn()`はメインターン、`cancelAllBackgroundAgents()`はバックグラウンドエージェントを個別に制御します。`remote()`と`visibility()`はリモート公開・共有状態、`sandbox()`はセッションのサンドボックス、`managedSettings()`はセッションスコープの管理設定、`contentExclusion()`はパス判定、`metadata()`はクライアント所有メタデータ、`canvas()`はCanvas連携を扱います。

### debug (experimental)

`debug()->collectLogs()`でセッションのイベントログ、プロセスログ、シェルログを `.tgz` アーカイブまたはディレクトリに収集できます。ログは既定で秘匿情報がリダクションされます。`additionalEntries`でファイルやディレクトリを追加でき、`redaction`には`plain-text`、`events-jsonl`、`none`を指定できます。`none`を指定するとリダクションされないため、追加する内容に注意してください。

```php
use Revolution\Copilot\Enums\DebugCollectLogsEntryKind;
use Revolution\Copilot\Enums\DebugCollectLogsRedaction;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsDestination;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsEntry;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsRequest;

$result = $session->rpc()->debug()->collectLogs(new DebugCollectLogsRequest(
    destination: DebugCollectLogsDestination::archive('/tmp/copilot-debug.tgz', noOverwrite: true),
    additionalEntries: [
        new DebugCollectLogsEntry(
            kind: DebugCollectLogsEntryKind::File,
            path: '/var/log/my-app.log',
            bundlePath: 'application/my-app.log',
            redaction: DebugCollectLogsRedaction::PlainText,
            required: true,
        ),
    ],
));
```

`DebugCollectLogsDestination::directory('/tmp/copilot-debug')`を指定するとステージングディレクトリに出力できます。`include`では収集するログや参照パスを個別に指定でき、結果の`entries`と`skippedEntries`から収集内容を確認できます。宛先・追加エントリのパスはCLIサーバー側のパスです。stdioでCLIを起動する標準構成ではアプリと同じホストですが、TCPで別ホストのCLIサーバーに接続している場合、呼び出し側マシンではなくサーバー側のパスを指定してください。

### contentExclusion (experimental)

セッションの作業ディレクトリ内にあるローカル絶対パスが、コンテンツ除外ポリシーの対象か確認できます。ファイルの内容を読み込んだり添付したりする前に呼び出してください。

```php
use Revolution\Copilot\Types\Rpc\ContentExclusionCheckPathsRequest;

$result = $session->rpc()->contentExclusion()->checkPaths(
    new ContentExclusionCheckPathsRequest(paths: [
        '/workspace/private.txt',
        '/workspace/public.txt',
    ]),
);

if ($result->isExcluded('/workspace/private.txt')) {
    // 除外対象、または安全に判定できません。
}
```

`checkPaths()` は絶対パスのみを受け付け、パスはセッションの作業ディレクトリ内にある必要があります。`checks` は入力と同じ順序で返り、重複したパスもそのまま保持されます。サポートされないパス、ファイルシステム、またはポリシー評価が利用できない場合は `available: false` と空の `checks` が返ります。この場合、`isExcluded()` は常に `true` を返すため、すべてのパスを除外対象として扱ってください。`available: true` でも結果にないパスは安全側に倒して除外対象と判定します。

### MCP Apps (experimental)

`$session->rpc()->mcp()->apps()`からMCP Apps用のRPCを呼び出せます。`listTools()`と`callTool()`では、呼び出し元のMCPサーバー名`originServerName`が必須です。`callTool()`の結果は自由形式のMCP結果として返されます。

```php
use Revolution\Copilot\Types\Rpc\MCPAppsDiagnoseRequest;
use Revolution\Copilot\Types\Rpc\MCPAppsListToolsRequest;
use Revolution\Copilot\Types\Rpc\McpAppsCallToolRequest;
use Revolution\Copilot\Types\Rpc\McpAppsReadResourceRequest;
use Revolution\Copilot\Types\Rpc\McpAppsSetHostContextDetails;
use Revolution\Copilot\Types\Rpc\McpAppsSetHostContextRequest;

$apps = $session->rpc()->mcp()->apps();

$tools = $apps->listTools(new MCPAppsListToolsRequest(
    serverName: 'my-server',
    originServerName: 'my-server',
));
$result = $apps->callTool(new McpAppsCallToolRequest(
    serverName: 'my-server',
    toolName: 'lookup',
    originServerName: 'my-server',
    arguments: ['query' => 'hello'],
));
$resource = $apps->readResource(new McpAppsReadResourceRequest(
    serverName: 'my-server',
    uri: 'ui://my-app/index.html',
));
$context = $apps->getHostContext();
$apps->setHostContext(new McpAppsSetHostContextRequest(
    context: new McpAppsSetHostContextDetails(theme: 'dark', locale: 'ja-JP'),
));
$diagnostics = $apps->diagnose(new MCPAppsDiagnoseRequest(serverName: 'my-server'));
```

`readResource()`はHTMLなどのリソースデータを返します。SDKはブラウザー描画やiframe操作を行わないため、取得した内容の描画は呼び出し側アプリケーションの責務です。

### 機能別の使用例

以下に、上記の機能の代表的な呼び出し例をまとめています。

```php
// model
$session->rpc()->model()->getCurrent();
// 現在のモデルにおける reasoning summary の表示ポリシーを取得する（experimental）
$policy = $session->rpc()->model()->getReasoningSummaries(new ModelGetReasoningSummariesRequest(
    persistedShowReasoning: true, // 未設定なら省略
    summariesVisible: true, // ホストUIで現在表示中かどうか
));
// $policy->ready、$policy->sessionShowsSummaries、$policy->visibility など
// 選択中のモデルを変えずに reasoning effort のみを変更（experimental）
$effort = $session->rpc()->model()->setReasoningEffort(
    new ModelSetReasoningEffortRequest(reasoningEffort: ReasoningEffort::HIGH),
);
// $effort->reasoningEffort
$session->rpc()->model()->switchTo(new ModelSwitchToRequest(modelId: 'gpt-4'));
// reasoningEffortを指定する場合（対応モデルのみ）
$session->rpc()->model()->switchTo(new ModelSwitchToRequest(modelId: 'claude-opus-4.7', reasoningEffort: ReasoningEffort::HIGH));
// reasoningSummaryを指定する場合
$session->rpc()->model()->switchTo(new ModelSwitchToRequest(modelId: 'claude-opus-4.7', reasoningSummary: 'concise'));
// contextTierを指定する場合（対応モデルのみ）
$session->rpc()->model()->switchTo(new ModelSwitchToRequest(modelId: 'gpt-4o', contextTier: 'long_context'));
// modelCapabilitiesをオーバーライドする場合
$session->rpc()->model()->switchTo(new ModelSwitchToRequest(
    modelId: 'gpt-4',
    modelCapabilities: new ModelCapabilitiesOverride(
        supports: new ModelCapabilitiesOverrideSupports(vision: true),
    ),
));
// セッションで利用可能なモデルの一覧取得（experimental）
$models = $session->rpc()->model()->list();
$models->auto?->tiers; // アカウントで利用可能なAuto routing preference
$session->rpc()->model()->list(new ModelListRequest(skipCache: true)); // キャッシュをスキップ

// Auto routing preference を変更する（selected modelは変えない、experimental）
$session->rpc()->model()->switchAutoTier(new ModelSwitchAutoTierRequest(autoTier: AutoTier::BALANCE));
$session->rpc()->model()->switchAutoTier(new ModelSwitchAutoTierRequest(autoTier: null)); // provider-defaultのAuto routingへ戻す

// ホスト側でセッションのモデル許可リストを設定・解除する（experimental）
$session->rpc()->model()->setAllowedModels(new ModelSetAllowedModelsRequest(allowedModels: ['gpt-5']));
$session->rpc()->model()->setAllowedModels(new ModelSetAllowedModelsRequest(allowedModels: null)); // 制限を解除

// setModel()ヘルパーでも同様にreasoningEffortやmodelCapabilitiesを指定可能
$session->setModel('claude-opus-4.7', ReasoningEffort::HIGH);
$session->setModel('claude-opus-4.7', 'high'); // 文字列でも指定可能
$session->setModel('gpt-4', modelCapabilities: ['supports' => ['vision' => true]]); // 配列でも指定可能
// autoTierを指定する場合（modelIdが'auto'の場合のみ有効）
$session->setModel('auto', autoTier: AutoTier::INTELLIGENCE);

// setAutoTier()ヘルパーでmodelを変更せずにAuto routing preferenceのみ変更（experimental）
$session->setAutoTier(AutoTier::EFFICIENCY);

// mode
$session->rpc()->mode()->get();
$modeResult = $session->rpc()->mode()->set(new ModeSetRequest(mode: 'plan'));
// $modeResult->modeApplied、$modeResult->modelChanged、$modeResult->confirmation など

// name
$session->rpc()->name()->get();
$session->rpc()->name()->set(new NameSetRequest(name: 'My Session'));
// ユーザーが名前を設定していない場合に、要約をセッション名として適用（experimental）
$autoName = $session->rpc()->name()->setAuto(new NameSetAutoRequest(summary: 'Summary of the session'));
// $autoName->applied

// plan
$session->rpc()->plan()->read();
$session->rpc()->plan()->update(new PlanUpdateRequest(content: '...'));
$session->rpc()->plan()->delete();
// SQLite todoリストを読み取り（プランモード）
$result = $session->rpc()->plan()->readSqlTodos();
// $result->rows - PlanSqlTodosRow の配列（id, title, description, status）

// SQLite todoリストと依存関係を読み取り（experimental）
$result = $session->rpc()->plan()->readSqlTodosWithDependencies();
// $result->rows - PlanSqlTodosRow の配列
// $result->dependencies - PlanSqlTodoDependency の配列（todo_id, depends_on）

// workspaces
$session->rpc()->workspaces()->getWorkspace();
$session->rpc()->workspaces()->listFiles();
$session->rpc()->workspaces()->readFile(new WorkspacesReadFileRequest(path: 'file.txt'));
$session->rpc()->workspaces()->createFile(new WorkspacesCreateFileRequest(path: 'file.txt', content: '...'));
$session->rpc()->workspaces()->statFile(new WorkspacesStatFileRequest(path: 'file.txt'));
$session->rpc()->workspaces()->createDirectory(new WorkspacesCreateDirectoryRequest(path: 'new/dir', recursive: true));
$session->rpc()->workspaces()->removePath(new WorkspacesRemovePathRequest(path: 'old/dir', recursive: true, force: true));
$session->rpc()->workspaces()->renamePath(new WorkspacesRenamePathRequest(source: 'old.txt', destination: 'new.txt'));

// instructions (セッションのインストラクションソースを取得)
$result = $session->rpc()->instructions()->getSources();
// $result->sources - InstructionsSources の配列
$session->rpc()->instructions()->reload();

// LSP設定を明示的に再読み込み（experimental）
$session->rpc()->lsp()->initialize([
    'workingDirectory' => base_path(),
    'gitRoot' => base_path(),
    'force' => true,
]);

// instructions discover (インストラクションファイルを検出)
use Revolution\Copilot\Types\Rpc\InstructionsDiscoverRequest;

$result = $session->rpc()->instructions()->discover();
// $result->sources - InstructionSource の配列
$result = $session->rpc()->instructions()->discover(new InstructionsDiscoverRequest(
    projectPaths: ['/path/to/project'],
    excludeHostInstructions: true, // ホストレベルのインストラクションを除外
));

// fleet
$session->rpc()->fleet()->start(new FleetStartRequest(prompt: '...'));
// 詳細は fleet-mode.md を参照

// agent
$session->rpc()->agent()->list();
$session->rpc()->agent()->getCurrent();
$session->rpc()->agent()->select(new AgentSelectRequest(agentId: '...'));
$session->rpc()->agent()->deselect();
$session->rpc()->agent()->reload();

// skills (experimental: スキルの管理)
$session->rpc()->skills()->list();
$session->rpc()->skills()->enable(new SkillsEnableRequest(name: 'skill-name'));
$session->rpc()->skills()->disable(new SkillsDisableRequest(name: 'skill-name'));
$session->rpc()->skills()->reload();
$session->rpc()->skills()->ensureLoaded(); // 未読込のスキルを読み込む（再読込はしない）
$invoked = $session->rpc()->skills()->getInvoked();
// $invoked->skills - セッション中に呼び出されたスキルの履歴

// mcp (experimental: MCPサーバーの管理)
$servers = $session->rpc()->mcp()->list();
// HTTP/SSEサーバーの設定URL: $servers->servers[0]->url
$session->rpc()->mcp()->enable(new McpEnableRequest(serverName: 'server-name'));
$session->rpc()->mcp()->disable(new McpDisableRequest(serverName: 'server-name'));
$session->rpc()->mcp()->reload();
// 進行中のMCPロードをバックグラウンドに移行(待機中のターンを解放)
$session->rpc()->mcp()->moveLoadingToBackground();

// セッション内のMCPサーバーを起動・再起動（永続設定は変更しない）
use Revolution\Copilot\Types\Rpc\McpRestartServerRequest;
use Revolution\Copilot\Types\Rpc\McpStartServerRequest;

$session->rpc()->mcp()->startServer(new McpStartServerRequest(
    serverName: 'server-name',
    config: ['type' => 'http', 'url' => 'https://mcp.example.com'],
));
$session->rpc()->mcp()->restartServer(new McpRestartServerRequest(
    serverName: 'server-name',
    expectedInstallationId: 'installation-id',
));

// 自動管理されているGitHub MCPサーバーの削除と環境変数値の解決方法
$removed = $session->rpc()->mcp()->removeGitHub();
$mode = $session->rpc()->mcp()->setEnvValueMode(['mode' => 'indirect']);
// $removed->removed、$mode->mode

// MCP samplingの実行とキャンセル
use Revolution\Copilot\Types\Rpc\McpCancelSamplingExecutionParams;
use Revolution\Copilot\Types\Rpc\McpExecuteSamplingParams;

$sampling = $session->rpc()->mcp()->executeSampling(new McpExecuteSamplingParams(
    requestId: 'sampling-1',
    serverName: 'server-name',
    mcpRequestId: 'mcp-request-1',
    request: ['messages' => [], 'maxTokens' => 256],
));
$cancelled = $session->rpc()->mcp()->cancelSamplingExecution(
    new McpCancelSamplingExecutionParams(requestId: 'sampling-1'),
);

// MCPサーバーが実行中かどうかを確認
use Revolution\Copilot\Types\Rpc\McpIsServerRunningRequest;

$result = $session->rpc()->mcp()->isServerRunning(new McpIsServerRunningRequest(serverName: 'my-server'));
// $result->running - サーバーが実行中かどうか

// MCPサーバーのツール一覧を取得
use Revolution\Copilot\Types\Rpc\McpListToolsRequest;

$result = $session->rpc()->mcp()->listTools(new McpListToolsRequest(serverName: 'my-server'));
// $result->tools - McpTools の配列（name, description）

// MCPプロンプトの一覧と取得（experimental）
use Revolution\Copilot\Types\Rpc\McpPromptsGetRequest;
use Revolution\Copilot\Types\Rpc\McpPromptsListRequest;

$prompts = $session->rpc()->mcp()->listPrompts(new McpPromptsListRequest(serverName: 'my-server'));
$rendered = $session->rpc()->mcp()->getPrompt(new McpPromptsGetRequest(
    serverName: 'my-server',
    promptName: 'summarize',
    arguments: ['document' => 'README.md'],
));
// $prompts->prompts - MCPプロンプト定義
// $rendered->messages - レンダリング済みプロンプト（モデルには送信されない）

// MCPサーバーを停止
use Revolution\Copilot\Types\Rpc\McpStopServerRequest;

$session->rpc()->mcp()->stopServer(new McpStopServerRequest(serverName: 'my-server'));

// MCPリソースの一覧を取得（experimental）
use Revolution\Copilot\Types\Rpc\McpResourcesListRequest;

$result = $session->rpc()->mcp()->resources()->list(new McpResourcesListRequest(serverName: 'my-server'));
// $result->resources - McpResource の配列（uri, name, title, mimeType など）
// $result->nextCursor - 次ページのカーソル（存在する場合）

// MCPリソースを取得（experimental）
use Revolution\Copilot\Types\Rpc\McpResourcesReadRequest;

$result = $session->rpc()->mcp()->resources()->read(new McpResourcesReadRequest(serverName: 'my-server', uri: 'ui://example'));
// $result->contents - McpResourceContent の配列

// MCPリソーステンプレートの一覧を取得（experimental）
use Revolution\Copilot\Types\Rpc\McpResourcesListTemplatesRequest;

$result = $session->rpc()->mcp()->resources()->listTemplates(new McpResourcesListTemplatesRequest(serverName: 'my-server'));
// MCP OAuthログイン（認証が必要なMCPサーバー向け）
use Revolution\Copilot\Types\Rpc\McpOauthCompleteRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginRequest;
use Revolution\Copilot\Types\Rpc\McpOauthProbeRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthCancelLoginRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthPrepareLoginRequest;

$result = $session->rpc()->mcp()->login(new McpOauthLoginRequest(serverName: 'my-server'));
// $result->authorizationUrl - OAuthフローのURL（認証が必要な場合）
// ホスト管理のOAuthリダイレクトフローでは redirectUri を指定し、callback後に complete() を呼び出す
$result = $session->rpc()->mcp()->login(new McpOauthLoginRequest(
    serverName: 'my-server',
    redirectUri: 'http://localhost:3000/oauth/callback',
));
$session->rpc()->mcp()->complete(new McpOauthCompleteRequest(
    authorizationId: $result->authorizationId,
    callbackUrl: $callbackUrl,
));

// OAuth状態を確認し、所有インストールに紐づいたログインを準備・キャンセル
$probe = $session->rpc()->mcp()->oauth()->probe(new McpOauthProbeRequest(serverName: 'my-server'));
$prepared = $session->rpc()->mcp()->oauth()->prepareLogin(new SessionMcpOauthPrepareLoginRequest(
    serverName: 'my-server',
    expectedInstallationId: 'installation-id',
));
$cancelled = $session->rpc()->mcp()->oauth()->cancelLogin(new SessionMcpOauthCancelLoginRequest(
    expectedInstallationId: 'installation-id',
    loginId: $prepared->loginId,
));

// plugins (experimental: プラグインの一覧)
$session->rpc()->plugins()->list();
// プラグインをリロード
$session->rpc()->plugins()->reload();
// オプションを指定してリロード
use Revolution\Copilot\Types\Rpc\PluginsReloadRequest;

$session->rpc()->plugins()->reload(new PluginsReloadRequest(
    reloadMcp: true,      // MCPサーバー設定を再読み込み
    reloadHooks: true,    // フックを再読み込み
    reloadCustomAgents: false, // カスタムエージェントを再読み込みしない
));

// extensions (experimental: エクステンションの管理)
$session->rpc()->extensions()->list();
$session->rpc()->extensions()->enable(new ExtensionsEnableRequest(id: 'project:my-ext'));
$session->rpc()->extensions()->disable(new ExtensionsDisableRequest(id: 'project:my-ext'));
$session->rpc()->extensions()->reconcile(); // host-only: 実効membershipを再同期し、settled listを返す
$session->rpc()->extensions()->reload();

// compaction → history に名前変更
$session->rpc()->history()->compact();
// 手動圧縮を中断する / バックグラウンド圧縮をキャンセルする
$session->rpc()->history()->abortManualCompaction();
$session->rpc()->history()->cancelBackgroundCompaction();
// 別セッションへの引き継ぎ用要約を取得する
$summary = $session->rpc()->history()->summarizeForHandoff()->summary;
// 特定のイベント以降の履歴を切り詰める
$session->rpc()->history()->truncate(new HistoryTruncateRequest(
    eventId: 'evt-123', // このイベントとそれ以降のすべてのイベントが削除される
));

// tools (プロトコルv3+: external_tool.requestedイベントへの応答)
// runtime tool setを送信前に初期化・検証（experimental。subagent sessionなどで使用）
$session->rpc()->tools()->initializeAndValidate();
// 空の ToolsInitializeAndValidateResult が返る

// 現在セッションに公開されているツールをネイティブの呼び出しパイプラインで実行（experimental）
use Revolution\Copilot\Types\Rpc\ToolsExecuteRequest;

$result = $session->rpc()->tools()->execute(new ToolsExecuteRequest(
    name: 'search',
    arguments: ['query' => 'Laravel'],
    toolCallId: 'call-123', // 任意。呼び出しとの相関に使用
));

if (is_string($result)) {
    // シンプルなテキスト結果
    echo $result;
} else {
    // ToolResultExpanded: textResultForLlm、resultType、newMessages、
    // mcpMeta、structuredContent、taskCompletionDecision など
    echo $result->textResultForLlm;
    // resultType は ToolResultType または未知の値を保持する string
}

// 配列でも指定可能。toolCallIdは省略でき、sessionIdはこのセッションの値で上書きされる
$session->rpc()->tools()->execute([
    'name' => 'search',
    'arguments' => ['query' => 'Copilot'],
]);
// arguments: [] はJSONオブジェクト {} として送信され、scalar/null/空でない配列はJSON値を保つ

// execute() は通常のネイティブツール呼び出しパイプラインを通るため、権限確認を迂回しない。
// ツールに承認が必要な場合は、通常どおりSessionConfigのonPermissionRequestハンドラーが処理する。
$session->rpc()->tools()->handlePendingToolCall(new HandlePendingToolCallRequest(
    requestId: '...',
    result: 'ツールの実行結果',
));
// セッションの現在のツールメタデータを取得（experimental）
$session->rpc()->tools()->getCurrentMetadata();
// この接続が提供する外部ツールを完全に置換（experimental）
$session->rpc()->tools()->set(['tools' => [
    ['name' => 'search', 'description' => 'Search documents', 'parameters' => ['type' => 'object']],
]]);
// 現在のセッションに適用されているサブエージェント設定を取得（experimental）
$settings = $session->rpc()->tools()->getSubagentSettings();
// $settings->subagents は SubagentSettings。設定ブロックがなければ null
// サブエージェントの設定を更新（experimental）
use Revolution\Copilot\Enums\SubagentSettingsEntryContextTier;
use Revolution\Copilot\Types\Rpc\SubagentSettings;
use Revolution\Copilot\Types\Rpc\SubagentSettingsEntry;
use Revolution\Copilot\Types\Rpc\UpdateSubagentSettingsRequest;

$result = $session->rpc()->tools()->updateSubagentSettings(new UpdateSubagentSettingsRequest(
    subagents: new SubagentSettings(agents: [
        'my-agent' => new SubagentSettingsEntry(
            contextTier: SubagentSettingsEntryContextTier::Default,
            model: 'gpt-5',
        ),
    ]),
));
// 設定を適用すると空の ToolsUpdateSubagentSettingsResult が返る

// permissions (プロトコルv3+: permission.requestedイベントへの応答)
use Revolution\Copilot\Types\Rpc\FolderTrustAddParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationAddToolApprovalParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationApplyParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationResolveParams;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsCommands;

$session->rpc()->permissions()->handlePendingPermissionRequest(new PermissionDecisionRequest(
    requestId: '...',
    result: PermissionDecision::approveOnce(),
));
// セッション内のすべての権限リクエストを自動承認
$session->rpc()->permissions()->setApproveAll(new PermissionsSetApproveAllRequest(enabled: true));
// 権限モードを設定 (manual / assisted / allow-all)
$session->rpc()->permissions()->setMode(new PermissionsSetModeRequest(mode: PermissionMode::AllowAll));
// 現在の権限モードを取得
$mode = $session->rpc()->permissions()->getMode();
// セッションスコープの権限承認をリセット
$session->rpc()->permissions()->resetSessionApprovals();
// 現在pending中の権限リクエスト一覧を取得
$pending = $session->rpc()->permissions()->pendingRequests();
// 権限リクエストのイベントブリッジを有効化/無効化
$session->rpc()->permissions()->setRequired(new PermissionsSetRequiredRequest(required: true));
// 権限ルールを追加/削除
$session->rpc()->permissions()->modifyRules(new PermissionsModifyRulesParams(
    scope: PermissionsModifyRulesScope::SESSION,
    add: [['tool' => 'bash', 'decision' => 'allow']],
));
// 権限プロンプトを表示したことを通知
$session->rpc()->permissions()->notifyPromptShown(new PermissionPromptShownNotification(message: 'tool permission prompt'));
// パス権限
$paths = $session->rpc()->permissions()->paths()->list();
$session->rpc()->permissions()->paths()->add(new PermissionPathsAddParams(path: '/path/to/allow'));
$session->rpc()->permissions()->paths()->updatePrimary(new PermissionPathsUpdatePrimaryParams(path: '/path/to/allow'));
$session->rpc()->permissions()->paths()->isPathWithinAllowedDirectories(new PermissionPathsAllowedCheckParams(path: '/path/to/allow/file.txt'));
$session->rpc()->permissions()->paths()->isPathWithinWorkspace(new PermissionPathsWorkspaceCheckParams(path: '/path/to/allow/file.txt'));
// URL権限
$session->rpc()->permissions()->urls()->setUnrestrictedMode(
    new PermissionUrlsSetUnrestrictedModeParams(enabled: true)
);
// 作業ディレクトリの権限ロケーションを解決して、保存済みルールを適用
$location = $session->rpc()->permissions()->locations()->resolve(
    new PermissionLocationResolveParams(workingDirectory: '/path/to/repo')
);
$session->rpc()->permissions()->locations()->apply(
    new PermissionLocationApplyParams(workingDirectory: '/path/to/repo')
);
// ツール承認を権限ロケーションに永続化
$session->rpc()->permissions()->locations()->addToolApproval(
    new PermissionLocationAddToolApprovalParams(
        approval: new PermissionsLocationsAddToolApprovalDetailsCommands(commandIdentifiers: ['bash']),
        locationKey: $location->locationKey,
    )
);
// フォルダ信頼状態の確認と追加
$trusted = $session->rpc()->permissions()->folderTrust()->isTrusted(['path' => '/path/to/repo']);
$session->rpc()->permissions()->folderTrust()->addTrusted(new FolderTrustAddParams(path: '/path/to/repo'));

// commands: セッションで利用可能なスラッシュコマンド一覧を取得
use Revolution\Copilot\Types\Rpc\CommandsListRequest;

$list = $session->rpc()->commands()->list();
// $list->commands - SlashCommandInfo の配列
// $list->commands[0]->name - コマンド名（スラッシュなし）
// $list->commands[0]->description - コマンド説明
// $list->commands[0]->kind - コマンドカテゴリ（builtin, skill, client）
// $list->commands[0]->allowDuringAgentExecution - エージェント実行中に使用可能かどうか

// フィルターを指定して一覧取得
$list = $session->rpc()->commands()->list(new CommandsListRequest(
    includeBuiltins: true,
    includeSkills: false,
));

// commands: スラッシュコマンドを呼び出す
use Revolution\Copilot\Types\Rpc\CommandsInvokeRequest;

$result = $session->rpc()->commands()->invoke(new CommandsInvokeRequest(
    name: 'help',
    input: 'optional input text',
));

// commands: コマンド呼び出しイベントへの応答
$session->rpc()->commands()->handlePendingCommand(new CommandsHandlePendingCommandRequest(
    requestId: '...',
));

// commands: スラッシュコマンドを同期実行
use Revolution\Copilot\Types\Rpc\ExecuteCommandParams;

$result = $session->rpc()->commands()->execute(new ExecuteCommandParams(
    commandName: 'review',
    args: 'src/',
), timeout: 120.0); // CLI 応答の待機時間（秒）。省略時は30秒
// $result->error - 実行に失敗した場合のエラーメッセージ（成功時は null）

// commands: キューに入ったコマンドへの応答
$session->rpc()->commands()->respondToQueuedCommand(new CommandsRespondToQueuedCommandRequest(
    requestId: '...',
    result: new QueuedCommandResult(handled: true),
));

// ui: UIエリシテーションリクエストへの応答
$session->rpc()->ui()->elicitation(new UIElicitationRequest(
    message: 'ユーザーへの質問',
    requestedSchema: ['type' => 'object', 'properties' => [...]],
));

// ui: 会話履歴に追加しない一時的な問い合わせ
$result = $session->rpc()->ui()->ephemeralQuery(new UIEphemeralQueryRequest(
    question: 'これまでの会話の要点を教えてください。',
));
// $result->answer

// ui: 保留中のエリシテーションリクエストへの応答（elicitation.requestedイベント経由）
$session->rpc()->ui()->handlePendingElicitation(new UIHandlePendingElicitationRequest(
    requestId: '...',
    result: ['action' => 'accept', 'content' => ['name' => 'John']],
));

// ui: ブロードキャストイベント経由で保留中のリクエストに応答
use Revolution\Copilot\Enums\UIAutoModeSwitchResponse;
use Revolution\Copilot\Types\Rpc\UIExitPlanModeResponse;
use Revolution\Copilot\Types\Rpc\UIHandlePendingAutoModeSwitchRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingExitPlanModeRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingSamplingRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingUserInputRequest;
use Revolution\Copilot\Types\Rpc\UIUserInputResponse;

$result = $session->rpc()->ui()->handlePendingUserInput(new UIHandlePendingUserInputRequest(
    requestId: '...',
    response: new UIUserInputResponse(answer: 'blue', wasFreeform: false),
));

$result = $session->rpc()->ui()->handlePendingExitPlanMode(new UIHandlePendingExitPlanModeRequest(
    requestId: '...',
    response: new UIExitPlanModeResponse(approved: true, selectedAction: 'autopilot_fleet'),
));

$result = $session->rpc()->ui()->handlePendingAutoModeSwitch(new UIHandlePendingAutoModeSwitchRequest(
    requestId: '...',
    response: UIAutoModeSwitchResponse::Yes,
));

$result = $session->rpc()->ui()->handlePendingSampling(new UIHandlePendingSamplingRequest(
    requestId: '...',
    response: ['result' => '...'],
));
// 各メソッドは UIHandlePendingResult を返す。$result->success で応答が受理されたか確認できる。

// SessionConfig の onExitPlanModeRequest / onAutoModeSwitchRequest は reverse-RPC で自動応答する。
// 同じリクエストをイベント経由で手動応答する場合は、両方の経路を重ねて使わないこと。

// log: セッションタイムラインへのメッセージ記録
$session->rpc()->log()->log(new LogRequest(message: '処理を開始しました'));
$session->rpc()->log()->log(new LogRequest(message: 'ディスク使用量が多い', level: LogLevel::WARNING));
$session->rpc()->log()->log(new LogRequest(message: 'エラーが発生しました', level: LogLevel::ERROR));
$session->rpc()->log()->log(new LogRequest(message: 'デバッグ情報', ephemeral: true));

// shell: セッション内でシェルコマンドを実行
$result = $session->rpc()->shell()->exec(new ShellExecRequest(command: 'ls -la'));
// $result->processId でプロセスIDを取得してkillや出力追跡に使用

$session->rpc()->shell()->exec(new ShellExecRequest(
    command: 'npm test',
    cwd: '/path/to/project',
    timeout: 60000, // ミリ秒
));

// 実行中のシェルプロセスを停止
$session->rpc()->shell()->kill(new ShellKillRequest(
    processId: $result->processId,
    signal: 'SIGTERM', // SIGTERM（デフォルト）, SIGKILL, SIGINT
));

// ユーザーがリクエストしたシェルコマンドをキャンセル
use Revolution\Copilot\Types\Rpc\ShellExecuteUserRequestedRequest;
use Revolution\Copilot\Types\Rpc\ShellCancelUserRequestedRequest;

$execution = $session->rpc()->shell()->executeUserRequested(new ShellExecuteUserRequestedRequest(
    requestId: 'req-123',
    command: 'pwd',
), timeout: 120.0); // CLI 応答の待機時間（秒）。省略時は30秒
// $execution->toolCallId / success / output / exitCode / error
// 実行中に別の処理からキャンセルする場合は、同じ requestId を指定する

$cancelled = $session->rpc()->shell()->cancelUserRequested(
    new ShellCancelUserRequestedRequest(requestId: 'req-123')
);
// $cancelled->cancelled - キャンセルに成功したかどうか

// usage (experimental: セッション使用量メトリクス)
$metrics = $session->rpc()->usage()->getMetrics();
// $metrics->totalPremiumRequestCost - プレミアムリクエストの合計コスト
// $metrics->totalUserRequests - ユーザーリクエストの合計数
// $metrics->codeChanges - コード変更メトリクス（追加行数、削除行数、変更ファイル数）
// $metrics->modelMetrics - モデルごとのトークン使用量とリクエスト数
// $metrics->currentModel - 現在のモデル識別子

// telemetry (experimental: セッションテレメトリー)
use Revolution\Copilot\Types\Rpc\TelemetrySetFeatureOverridesRequest;

$telemetry = $session->rpc()->telemetry();
$engagement = $telemetry->getEngagementId();
// $engagement->engagementId - 利用可能な場合のエンゲージメントID。取得できない場合はnull

$telemetry->setFeatureOverrides(new TelemetrySetFeatureOverridesRequest(
    features: ['feature-key' => 'override-value'],
));
// 指定したoverrideは以後のテレメトリーイベントに付加され、以前のoverrideを置き換えます。
// SessionConfig::$enableSessionTelemetry（内部テレメトリーの有効化）とは別の機能です。

// auth: セッションの認証状態を取得
$status = $session->rpc()->auth()->getStatus();
// $status->isAuthenticated - 認証済みかどうか
// $status->authType - 認証タイプ（AuthInfoType enum: gh-cli, token, env など）
// $status->login - GitHubログイン名
// $status->host - GitHubホスト
// $status->copilotPlan - Copilotプラン（individual, business など）
// $status->statusMessage - 認証状態のメッセージ

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

// 特定モデルのエンドポイントを取得
$endpoint = $session->rpc()->provider()->getEndpoint(
    new ProviderGetEndpointRequest(model: 'gpt-5')
);

// provider: ホスト管理モデルをBYOKレジストリから取り下げる (experimental)
$result = $session->rpc()->provider()->withdraw(['models' => ['provider/model']]);
// $result->withdrawn, $result->providersRemoved, $result->modelDeselected

// queue: キューに積まれたユーザー向け項目の確認・管理
$pending = $session->rpc()->queue()->pendingItems();
// $pending->items - QueuePendingItems の配列
// $pending->items[0]->kind - QueuePendingItemsKind（Command / Message）

$removed = $session->rpc()->queue()->removeMostRecent();
// $removed->removed - 直近項目を削除できたかどうか

$session->rpc()->queue()->clear();

// 指定位置に項目を挿入（position は 0 始まり）
use Revolution\Copilot\Types\Rpc\QueueInsertAtRequest;
use Revolution\Copilot\Types\Rpc\QueueInsertMessage;

$queue = $session->rpc()->queue();
$inserted = $queue->insertAt(new QueueInsertAtRequest(
    position: 0,
    message: new QueueInsertMessage(prompt: 'Review the changes'),
));
// $inserted->id - 挿入された項目の ID

$queue->moveItem(['id' => $inserted->id, 'toPosition' => 1]);
$duplicated = $queue->duplicateAt(['id' => $inserted->id]);
$queue->updateText(['id' => $duplicated->id, 'prompt' => 'Review and test the changes']);
$removedAt = $queue->removeAt(['id' => $duplicated->id]);
// $removedAt->removed - 項目を削除できたかどうか

$sent = $queue->sendNow(['id' => $inserted->id]);
// $sent->steered - 実行中のターンに送られたかどうか

// ドレインを一時停止する。停止は自動解除されないため、必ず false で再開する
$queue->setDrainPaused(['paused' => true]);
$queue->setDrainPaused(['paused' => false]);

// schedule (experimental: スケジュール済みプロンプト管理)
$schedule = $session->rpc()->schedule()->list();
// $schedule->entries - ScheduleEntry の配列

use Revolution\Copilot\Types\Rpc\ScheduleStopRequest;

$stopped = $session->rpc()->schedule()->stop(new ScheduleStopRequest(id: 1));
// $stopped->stopped - 停止に成功したかどうか

// tasks (experimental: バックグラウンドタスク管理)
// エージェントタスクを開始してIDを取得
use Revolution\Copilot\Types\Rpc\TasksStartAgentRequest;

$result = $session->rpc()->tasks()->startAgent(new TasksStartAgentRequest(
    agentType: 'explore',
    prompt: 'Search for all TODO comments in the codebase',
    name: 'todo-search',
    description: 'Find TODO items', // 任意
    model: 'gpt-4o', // 任意
));
// $result->agentId - バックグラウンドタスクのID

// 現在のタスク一覧を取得
$list = $session->rpc()->tasks()->list();
// $list->tasks - TaskAgentInfo、TaskShellInfo、TaskClientInfo の配列

// 切り離されたシェルの出力をカーソルで増分読み取り
use Revolution\Copilot\Types\Rpc\TasksReadOutputRequest;

$cursor = null;
do {
    $output = $session->rpc()->tasks()->readOutput(new TasksReadOutputRequest(
        id: 'shell-1',
        cursor: $cursor,
        waitMs: 1000,
    ));
    echo $output->text;
    $cursor = $output->cursor;
} while ($output->exit === null);
// $output->exit->status - タスクの終了状態
// $output->exit->exitCode - プロセスの終了コード（nullable）

// 1 回の読み取りは最大 64 KiB。waitMs は既定値 0、最大 30000ms。
// stdout と stderr は統合される。カーソルが無効になった場合はカーソルなしで再読み取りする。

// アプリケーション所有のジョブをタスクとして登録
use Revolution\Copilot\Types\Rpc\TasksRegisterRequest;

$registered = $session->rpc()->tasks()->register(new TasksRegisterRequest(
    clientTaskId: 'import-123',
    description: '顧客データをインポート',
    cancellable: true,
    displayName: '顧客インポート', // 任意
));
// $registered->task->id - CLI が割り当てたタスクID
// $registered->created / $registered->reclaimed - 新規作成・孤立タスクの再取得状態

// クライアント所有タスクの進捗を更新
use Revolution\Copilot\Enums\TaskClientActiveStatus;
use Revolution\Copilot\Enums\TaskClientUpdateKind;
use Revolution\Copilot\Types\Rpc\TaskClientUpdate;
use Revolution\Copilot\Types\Rpc\TasksUpdateRequest;

$updated = $session->rpc()->tasks()->update(new TasksUpdateRequest(
    id: $registered->task->id,
    sequence: 1,
    update: new TaskClientUpdate(
        kind: TaskClientUpdateKind::Progress,
        status: TaskClientActiveStatus::Running,
        phase: 'importing',
        percentage: 25,
    ),
));
// sequence は更新ごとに増加させる

// 完了を報告
$completed = $session->rpc()->tasks()->update(new TasksUpdateRequest(
    id: $registered->task->id,
    sequence: 2,
    update: new TaskClientUpdate(
        kind: TaskClientUpdateKind::Completed,
        result: ['imported' => 100],
    ),
));

// タスクの進捗を取得（未追跡の場合は null）
$progress = $session->rpc()->tasks()->getProgress(['id' => $registered->task->id])->progress;
// TaskAgentProgress、TaskClientProgress、TaskShellProgress、または null

// 切り離されたシェルの状態を更新し、バックグラウンド作業の完了を待つ
$session->rpc()->tasks()->refresh();
$session->rpc()->tasks()->waitForPending();

// 現在バックグラウンド化できるタスクを取得、またはそのタスクを即時にバックグラウンド化
$promotable = $session->rpc()->tasks()->getCurrentPromotable()->task;
$promotedCurrent = $session->rpc()->tasks()->promoteCurrentToBackground()->task;

// タスクをバックグラウンドモードに移行（同期待ちを解除）
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundRequest;

$promoted = $session->rpc()->tasks()->promoteToBackground(
    new TasksPromoteToBackgroundRequest(id: $result->agentId)
);
// $promoted->promoted - 移行に成功したかどうか

// タスクをキャンセル
use Revolution\Copilot\Types\Rpc\TasksCancelRequest;

$cancelled = $session->rpc()->tasks()->cancel(
    new TasksCancelRequest(id: $result->agentId)
);
// $cancelled->cancelled - キャンセルに成功したかどうか

// 完了またはキャンセル済みのタスクを削除
use Revolution\Copilot\Types\Rpc\TasksRemoveRequest;

$removed = $session->rpc()->tasks()->remove(
    new TasksRemoveRequest(id: $result->agentId)
);
// $removed->removed - 削除に成功したかどうか

// 実行中のエージェントタスクにメッセージを送信（ステアリング）
use Revolution\Copilot\Types\Rpc\TasksSendMessageRequest;

$sent = $session->rpc()->tasks()->sendMessage(
    new TasksSendMessageRequest(id: $result->agentId, message: '別のディレクトリも検索してください')
);
// $sent->sent - メッセージが正常に送信されたかどうか
// $sent->error - 送信失敗時のエラーメッセージ（nullable）

// メインターンだけを中断し、キュー内のプロンプトは次のターン用に保持
use Revolution\Copilot\Types\Rpc\InterruptMainTurnRequest;

$interrupted = $session->rpc()->interruptMainTurn(
    new InterruptMainTurnRequest(flushQueued: true)
);
// $interrupted->interrupted - 実行中のメインターンを中断できたかどうか

// メインターンを中断せず、実行中のバックグラウンドエージェントをすべてキャンセル
$cancelledCount = $session->rpc()->cancelAllBackgroundAgents();
// キャンセルされたエージェント数
```

### remote (experimental: リモートセッションサポート)

```php
use Revolution\Copilot\Enums\RemoteSessionMode;
use Revolution\Copilot\Types\Rpc\RemoteEnableRequest;
use Revolution\Copilot\Types\Rpc\RemoteEnableResult;

// リモートセッションを有効化
// GitHubリポジトリのワーキングディレクトリで実行すると、
// GitHub WebやモバイルからセッションにアクセスできるURLが返される
$result = $session->rpc()->remote()->enable();
// $result->remoteSteerable - リモートステアリングが有効かどうか
// $result->url - GitHub フロントエンドURL（nullの場合あり）

// モードを指定して有効化
// RemoteSessionMode::Off - ローカルのみ（デフォルト）
// RemoteSessionMode::Export - セッションイベントをGitHubにエクスポート（リモートステアリングなし）
// RemoteSessionMode::On - エクスポートとリモートステアリングを両方有効化
$result = $session->rpc()->remote()->enable(new RemoteEnableRequest(
    mode: RemoteSessionMode::Export,
));

// リモートセッションを無効化
$session->rpc()->remote()->disable();

// リモートセッションに接続（experimental）
use Revolution\Copilot\Types\Rpc\ConnectRemoteSessionParams;

$connected = $session->rpc()->remote()->connectRemoteSession(new ConnectRemoteSessionParams(
    sessionId: 'remote-session-id',
));
// $connected->sessionId - 接続したセッションのID
// $connected->metadata - ConnectedRemoteSessionMetadata オブジェクト
```

詳細は [Remote Sessions](./remote-sessions.md) を参照してください.

### sandbox (experimental: サンドボックス制御)

```php
// セッション作成前にホストのサンドボックス対応状況を確認
$hostSupport = $client->rpc()->sandbox()->getHostSupport();
// $hostSupport->supported, $hostSupport->reason, $hostSupport->capabilities

// Proxy CAの要否とインストール対応状況を確認
$proxyCa = $client->rpc()->sandboxProxyCa();
$relevant = $proxyCa->checkRelevance(['enabled' => true]);
$installable = $proxyCa->checkInstallationSupport();

use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionRequest;
use Revolution\Copilot\Types\Rpc\SandboxGrantPathForRequestRequest;

// サンドボックス強制状態を取得
$status = $session->rpc()->sandbox()->getEnforcementStatus();

// サンドボックスの新規shellで利用できる可能性がある認証情報（値は返されない）
$credentials = $session->rpc()->sandbox()->getCredentialSuggestions();
// $credentials->suggestions は SandboxCredentialSuggestion の配列
// sandbox無効時や候補がない場合は空配列。設定やネットワーク権限は変更されない。

// 保留中のサンドボックスバイパス許可リクエストを承認し、セッション全体でサンドボックスを無効化
$result = $session->rpc()->sandbox()->disableForSession(new SandboxDisableForSessionRequest(requestId: $requestId));
// $result->success, $result->enabled

// 保留中のサンドボックスエスカレーション許可リクエストが提示したパス許可（sandboxPathGrant）を
// 承認し、そのパスをセッションのサンドボックスポリシーに追加して、ブロックされた操作を
// サンドボックス内で再実行させる
$result = $session->rpc()->sandbox()->grantPathForRequest(new SandboxGrantPathForRequestRequest(requestId: $requestId));
// 配列入力も可: ->grantPathForRequest(['requestId' => $requestId])
// $result->success
// 対象のリクエストが保留中でなくなっていたり、sandboxPathGrantを伴わなかったり、
// 現在の管理ポリシー下でグラントが無効な場合は失敗する。
// 特定のパスのみを許可する最小権限の手段であり、セッション全体を無効化するdisableForSession()より推奨される。
// このパスは永続化されない（このセッション限りで、次回以降のセッションには引き継がれない）。
// サンドボックス設定を保存するホストは、必要であれば自身でパスを保存する。
```

権限ハンドラでは、`$request['sandboxPathGrant']`がある場合に対象の保留中リクエストの`requestId`で呼び出します。このRPC自身が許可リクエストに応答するため、成功した場合は通常の応答を送らないよう`PermissionDecision::noResult()`を返します。

```php
if (isset($request['sandboxPathGrant'])) {
    $result = $session->rpc()->sandbox()->grantPathForRequest([
        'requestId' => $requestId,
    ]);

    return $result->success
        ? PermissionDecision::noResult()
        : PermissionDecision::reject();
}
```

### metadata (experimental: セッション情報)

```php
use Revolution\Copilot\Types\Rpc\MetadataUpdateClientMetadataRequest;

// セッションの現在のアクティビティ状態を取得
$activity = $session->rpc()->metadata()->activity();
// $activity->abortable - 中断可能な処理があるか
// $activity->hasActiveWork - ターンやバックグラウンドタスクなどの作業が実行中か

// このローカルセッションに紐づくクライアント所有メタデータを取得
$metadata = $session->rpc()->metadata()->getClientMetadata();

// メタデータをアトミックに更新（clear → remove → set の順で適用）
$metadata = $session->rpc()->metadata()->updateClientMetadata(new MetadataUpdateClientMetadataRequest(
    set: ['my-app/key' => 'value'],
));
```

### Session canvas

Canvas RPC は `$session->rpc()->canvas()` から呼び出します。`list()` は
`CanvasList` の `canvases`、`listOpen()` は `CanvasListOpenResult` の
`openCanvases` に一覧を返します。`open()` は `OpenCanvasInstance`、
action 呼び出しは `CanvasActionInvokeResult` を返します。`close()` の
戻り値はありません。

```php
$canvases = $session->rpc()->canvas();
$available = $canvases->list()->canvases;
$openInstances = $canvases->listOpen()->openCanvases;

$instance = $canvases->open([
    'canvasId' => 'editor',
    'instanceId' => 'editor-1',
    'input' => ['file' => 'README.md'],
]);

$result = $canvases->action()->invoke([
    'instanceId' => $instance->instanceId,
    'actionName' => 'format',
    'input' => ['style' => 'compact'],
]);

$canvases->close(['instanceId' => $instance->instanceId]);
```

Canvas provider の reverse-RPC (`canvas.open` / `canvas.close` /
`canvas.action.invoke`) は `SessionConfig` / `ResumeSessionConfig` の
`canvasHandler` で処理します。Canvas の描画はホスト UI の責務です。

### workflow

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

### SessionFS コールバック型

セッションスコープのファイルシステム操作のためのコールバック型（Request/Result）が定義されています。これらはCopilot CLIがクライアントにコールバックする際のリクエスト/レスポンスの型です。

| 型クラス | 用途 |
|---|---|
| `SessionFsReadFileRequest` / `SessionFsReadFileResult` | ファイル読み取り |
| `SessionFsWriteFileRequest` | ファイル書き込み |
| `SessionFsAppendFileRequest` | ファイル追記 |
| `SessionFsExistsRequest` / `SessionFsExistsResult` | ファイル存在確認 |
| `SessionFsStatRequest` / `SessionFsStatResult` | ファイルメタデータ取得 |
| `SessionFsMkdirRequest` | ディレクトリ作成 |
| `SessionFsReaddirRequest` / `SessionFsReaddirResult` | ディレクトリ一覧 |
| `SessionFsReaddirWithTypesRequest` / `SessionFsReaddirWithTypesResult` | 型付きディレクトリ一覧 |
| `SessionFsRmRequest` | ファイル/ディレクトリ削除 |
| `SessionFsRenameRequest` | ファイル/ディレクトリ名前変更 |

`SessionFsStatResult` / `SessionFsReaddirResult` / `SessionFsReaddirWithTypesResult` は失敗時に使う任意の `error`（`SessionFsError`）を持ちます。

#### SessionFsハンドラー

Clientは`sessionFs.readFile` / `readFileBytes` / `writeFile` / `writeFileBytes` に加えて、`appendFile`、`exists`、`stat`、`mkdir`、`readdir`、`readdirWithTypes`、`rm`、`rename`、`sqliteQuery`、`sqliteTransaction`、`sqliteExists` の各リクエストを`Session`のプロバイダーへ振り分けます。ファイル操作の失敗は `ENOENT`（`ENOENT`または errno 2）/ `UNKNOWN` の`error`として返します。`exists`は例外時に`false`、`stat`は失敗時にゼロ値＋`error`を返します。プロバイダーが対応していない操作は`UNKNOWN`エラー（SQLite系はJSON-RPCエラー）になります。`sqliteQuery`/`sqliteExists`の例外はJSON-RPCエラー、`sqliteTransaction`の失敗は結果の`error`（`errorClass`付き）として返します。

プロバイダー側のインターフェース（`SessionFsFilesystemProvider`、`SessionFsSqliteCapableProvider`、`SessionFsSqliteProvider`、`SessionFsSqliteTransactionProvider`）は、従来の`SessionFsProvider`（2メソッド）を壊さないよう別インターフェースとして追加されています。

#### SQLite コールバック型

| 型クラス | 用途 |
|---|---|
| `SessionFsSetProviderCapabilities` | プロバイダーの対応機能宣言（SQLiteサポートなど） |
| `SessionFsSqliteExistsRequest` / `SessionFsSqliteExistsResult` | SQLiteデータベースの存在確認 |
| `SessionFsSqliteQueryRequest` / `SessionFsSqliteQueryResult` | SQLiteクエリ実行 |
| `SessionFsSqliteTransactionRequest` / `SessionFsSqliteTransactionStatement` / `SessionFsSqliteTransactionResult` | SQLiteトランザクション（複数ステートメントをアトミックに実行） |
| `SessionFsSqliteTransactionError` | トランザクション失敗の分類（`errorClass`: `busyOrLocked` / `fatal` / `postCommitAmbiguous`。enum `SessionFSSqliteTransactionErrorClass`） |

SQLiteサポートを有効にするには、`SessionFsSetProviderRequest` の `capabilities` に `SessionFsSetProviderCapabilities(sqlite: true)` を渡します。

```php
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderCapabilities;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderRequest;

$client->rpc()->sessionFs()->setProvider(new SessionFsSetProviderRequest(
    initialCwd: '/app',
    sessionStatePath: '.copilot/sessions',
    capabilities: new SessionFsSetProviderCapabilities(sqlite: true),
));
```

通常はこのRPCを直接呼ばず、Clientオプション`sessionFs`（または`config/copilot.php`の`session_fs`）を指定すると`Client::start()`が`sessionFs.setProvider`を1回だけ送信します。セッションには`createSessionFsProvider`が必要です（[SessionConfig](./session-config.md)を参照）。

`SessionFsSqliteQueryType` enum（`src/Enums/`）はクエリ種別を表します:
- `Exec`: DDL/複数ステートメント（結果なし）
- `Query`: SELECT（行を返す）
- `Run`: INSERT/UPDATE/DELETE（rowsAffected を返す）

これらの型クラスは`src/Types/Rpc/`に配置されています。

## 配列での引数指定

引数も返り値も専用のクラスを使っていますが引数は配列で指定することも可能です。

```php
$session->rpc()->mode()->set(['mode' => 'plan']);
```

## Testing

`Copilot::fake()`でのモックは使えないので`Copilot::expects('client')`や`Copilot::expects('start')`でモックしてください。
