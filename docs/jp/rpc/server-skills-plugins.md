# Skills・Plugins・Agents・Instructions・Extensions

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
use Revolution\Copilot\Enums\CatalogCandidateKind;
use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\CatalogSelectionDecision;
use Revolution\Copilot\Enums\CatalogTrustTier;
use Revolution\Copilot\Enums\SkillInstallationManagementOutcomeKind;
use Revolution\Copilot\Enums\SkillInstallationManagementResultKind;
use Revolution\Copilot\Enums\SkillsRemoveSourceOutcomeKind;
use Revolution\Copilot\Types\Rpc\AgentsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogGetGeneratedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogGetSavedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogMaterializationReference;
use Revolution\Copilot\Types\Rpc\CatalogRecommendRequest;
use Revolution\Copilot\Types\Rpc\CatalogSearchRequest;
use Revolution\Copilot\Types\Rpc\CatalogSelectionRequest;
use Revolution\Copilot\Types\Rpc\InstructionsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\InstructionsGetDiscoveryPathsRequest;
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
use Revolution\Copilot\Types\Rpc\SkillApplyInstallRequest;
use Revolution\Copilot\Types\Rpc\SkillApplyUninstallRequest;
use Revolution\Copilot\Types\Rpc\SkillInstallationOperationRequest;
use Revolution\Copilot\Types\Rpc\SkillInstallationsRequest;
use Revolution\Copilot\Types\Rpc\SkillPlanInstallRequest;
use Revolution\Copilot\Types\Rpc\SkillPlanInstallFromReferenceRequest;
use Revolution\Copilot\Types\Rpc\SkillPlanUninstallRequest;
use Revolution\Copilot\Types\Rpc\SkillSetEnabledRequest;
use Revolution\Copilot\Types\Rpc\SkillsInstallSourceRequest;
use Revolution\Copilot\Types\Rpc\SkillsRemoveSourceRequest;
use Revolution\Copilot\Types\Rpc\SkillsGetDiscoveryPathsRequest;

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

// HTTPS URLまたはローカルファイルから直接インストール (experimental)
$skills = Copilot::client()->rpc()->skills();
$installed = $skills->installSource(new SkillsInstallSourceRequest(
    source: 'https://example.test/skills/review/SKILL.md',
    cwd: '/workspace',
    personalSkillsDir: '/home/user/.copilot/skills',
    projectSkillsDir: '/workspace/.copilot/skills',
    project: false,
    skillDirectories: ['/custom/skills'],
    userAgent: 'my-app/1.0',
));
// 戻り値のskillDirectoriesを呼び出し側で保存する
$skillDirectories = $installed->skillDirectories;

// discovery directoryの登録解除。ディレクトリの内容は削除されない
$removed = $skills->removeSource(new SkillsRemoveSourceRequest(
    target: '/custom/skills',
    cwd: '/workspace',
    skillDirectories: $skillDirectories,
));
if ($removed->kind === SkillsRemoveSourceOutcomeKind::Removed) {
    $skillDirectories = $removed->result->skillDirectories;
}
// ファイル削除にinventoryが必要な場合、指定しないとneeds-inventoryが返り変更されない

// catalog.search → catalog.select でスキル候補を選択 (experimental)

$contract = new CatalogClientContract(1, ['catalog-selection', 'ai-skill-installation']);
$catalog = Copilot::client()->rpc()->catalog();

// 製品名と完全なタスクから公式製品の推奨候補を取得 (experimental)
$recommendations = $catalog->recommend(new CatalogRecommendRequest(
    contract: new CatalogClientContract(1, [CatalogCapability::CatalogRecommend->value]),
    query: 'Review this repository and identify security issues.',
    product: 'GitHub Copilot',
));

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
// 同名の直接インストールを区別する場合は、list() の directSourceId を names に指定
$plugins->enable(new PluginsEnableRequest(names: ['opaque-direct-source-id']));
$plugins->disable(new PluginsDisableRequest(names: ['opaque-direct-source-id']));

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
```

## Agent Plugin catalog候補

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
