# Tools・Permissions・Commands・UI

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
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
use Revolution\Copilot\Types\Rpc\SessionPermissionsManagedReviewRequiredRequest;

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
// セッション作成・再開時に permissionMode を指定した場合、適用結果はセッションから取得できる
$result = $session->permissionModeResult();
// $result->success - 適用に成功したか
// $result->mode - 適用後の権限モード
// 現在のポリシーで利用可能な承認モードを取得 (experimental)
$capabilities = $session->rpc()->permissions()->getCapabilities();
// $capabilities->assistedApproval - LLM支援の承認が有効か
// $capabilities->bypassPermissions - 管理ポリシーが無条件承認を許可しているか
// 有効な権限昇格と起動時のベースラインを取得
$allowAll = $session->rpc()->permissions()->getAllowAll();
// $allowAll->active / $allowAll->baseline->tools
// 権限要求イベントが必須かを取得
$required = $session->rpc()->permissions()->getRequired();
// 管理ポリシーが指定した権限要求のレビューを必要とするかを取得
$reviewRequired = $session->rpc()->permissions()->managedReviewRequired(
    new SessionPermissionsManagedReviewRequiredRequest(request: ['kind' => 'tool', 'toolName' => 'bash'])
);
// モデルコンテキストに含まれる追加ディレクトリを取得
$additionalDirectories = $session->rpc()->permissions()->additionalDirectories()->list();
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
use Revolution\Copilot\Types\Rpc\SessionUiRegisterDirectAutoModeSwitchHandlerRequest;
use Revolution\Copilot\Types\Rpc\UIUnregisterDirectAutoModeSwitchHandlerRequest;
use Revolution\Copilot\Types\Rpc\UIUserInputResponse;

$registration = $session->rpc()->ui()->registerDirectAutoModeSwitchHandler(
    new SessionUiRegisterDirectAutoModeSwitchHandlerRequest(autoModeRecovery: true),
);
// autoModeRecovery が true の場合、返された値が true であることを確認してから回復対応を有効にする。
// 実際のイベントリスナーは別途登録する。ハンドラーを削除する時は同じ handle を使って解除する。
$session->rpc()->ui()->unregisterDirectAutoModeSwitchHandler(
    new UIUnregisterDirectAutoModeSwitchHandlerRequest(handle: $registration->handle),
);

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

// auto_mode_recovery_consent.requested の data.requestId にも同じ RPC で応答する。
// 回復の再試行や巻き戻しはランタイムが行うため、アプリ側でプロンプトを再送しない。

$result = $session->rpc()->ui()->handlePendingSampling(new UIHandlePendingSamplingRequest(
    requestId: '...',
    response: ['result' => '...'],
));
// 各メソッドは UIHandlePendingResult を返す。$result->success で応答が受理されたか確認できる。

// SessionConfig の onExitPlanModeRequest / onAutoModeSwitchRequest は reverse-RPC で自動応答する。
// 同じリクエストをイベント経由で手動応答する場合は、両方の経路を重ねて使わないこと。
```
