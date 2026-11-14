# モデル・モード・名前・プラン

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

`model()->startupAdmission()`は、カタログの取得や認証情報の解決を行わず、キャッシュ済みの起動時モデル状態を取得する実験的RPCです。起動時設定を適用したあと、対話型ホストが`claimEnablement: true`を指定すると、カタログが準備済みの場合に限り、セッション内で1回限りの有効化プロンプトを要求できます。要求は所有ホストと共有され、セッションの存続期間に限定されます。結果の`selectedModel`、`catalogReady`、`requiresEnablement`で状態を確認でき、今回要求を消費した場合は`claimedEnablement`が`true`になり、`modelToEnable`に対象モデルが入ります。

```php
// model
$session->rpc()->model()->getCurrent();
// 起動時の状態を読むだけの場合は引数を省略
$admission = $session->rpc()->model()->startupAdmission();
// 有効化プロンプトの表示権を要求（配列またはModelStartupAdmissionRequest）
$admission = $session->rpc()->model()->startupAdmission(['claimEnablement' => true]);
// $admission->claimedEnablement が true のとき $admission->modelToEnable を確認
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

// Copilot Fusion tier を変更する（selected modelは変えない、experimental）
$session->rpc()->model()->switchFusionTier(new ModelSwitchFusionTierRequest(fusionTier: FusionTier::MAX));

// ホスト側でセッションのモデル許可リストを設定・解除する（experimental）
$session->rpc()->model()->setAllowedModels(new ModelSetAllowedModelsRequest(allowedModels: ['gpt-5']));
$session->rpc()->model()->setAllowedModels(new ModelSetAllowedModelsRequest(allowedModels: null)); // 制限を解除

// setModel()ヘルパーでも同様にreasoningEffortやmodelCapabilitiesを指定可能
$session->setModel('claude-opus-4.7', ReasoningEffort::HIGH);
$session->setModel('claude-opus-4.7', 'high'); // 文字列でも指定可能
$session->setModel('gpt-4', modelCapabilities: ['supports' => ['vision' => true]]); // 配列でも指定可能
// autoTierを指定する場合（modelIdが'auto'の場合のみ有効）
$session->setModel('auto', autoTier: AutoTier::INTELLIGENCE);
// fusionTierを指定する場合（modelIdが'fusion'または'auto'の場合のみ有効、autoTierとは併用不可）
$session->setModel('fusion', fusionTier: FusionTier::HIGH);

// setAutoTier()ヘルパーでmodelを変更せずにAuto routing preferenceのみ変更（experimental）
$session->setAutoTier(AutoTier::EFFICIENCY);

// setFusionTier()ヘルパーでmodelを変更せずにCopilot Fusion tierのみ変更（experimental）
$session->setFusionTier(FusionTier::STANDARD);

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
```
