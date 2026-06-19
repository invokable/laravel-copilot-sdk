# RPC(Remote Procedure Call)

公式SDKでは最近の新機能は`api.schema.json`を元にした自動コード生成で対応しています。Laravel版では生成後の他言語版を参考に同じように実装しています。

## ServerRpc

Clientに紐づくRPCクラス。

```php
use Revolution\Copilot\Facades\Copilot;
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

### メソッドリスト

```php
Copilot::client()->rpc()->ping();

// models
Copilot::client()->rpc()->models()->list();
// セッションごとのGitHubトークンで取得
Copilot::client()->rpc()->models()->list(new ModelsListRequest(gitHubToken: $token));

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

// user settings (ユーザー設定)
// インメモリキャッシュをクリア（次回読み込み時にディスクから再取得）
Copilot::client()->rpc()->userSettings()->reload();

// mcp discover (MCPサーバーの自動検出)
Copilot::client()->rpc()->mcp()->discover(new McpDiscoverRequest(
    workingDirectory: '/path/to/project',
));
// 引数なしで実行可能
$result = Copilot::client()->rpc()->mcp()->discover();
// $result->servers は DiscoveredMcpServer の配列

// sessionFs (セッションファイルシステムプロバイダーの登録)
Copilot::client()->rpc()->sessionFs()->setProvider(new SessionFsSetProviderRequest(
    initialCwd: '/path/to/project',
    sessionStatePath: '.copilot/sessions',
    conventions: 'posix',
));

// sessions (experimental: セッションのフォーク)
Copilot::client()->rpc()->sessions()->fork(new SessionsForkRequest(
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

// skills: スキル作成可能ディレクトリを取得 (experimental)
// スキルを配置するとランタイムが認識するディレクトリの一覧
use Revolution\Copilot\Types\Rpc\SkillsGetDiscoveryPathsRequest;

$result = Copilot::client()->rpc()->skills()->getDiscoveryPaths();
// $result->paths は SkillDiscoveryPath の配列
// $result->paths[0]->path - ディレクトリパス
// $result->paths[0]->scope - スコープ（user, project, plugin）
// $result->paths[0]->preferredForCreation - 新規作成時の推奨ディレクトリか
// オプションでスコープを絞り込み
$result = Copilot::client()->rpc()->skills()->getDiscoveryPaths(
    new SkillsGetDiscoveryPathsRequest(scope: 'project')
);

// agents (experimental: カスタムエージェント管理)
// カスタムエージェントの探索
use Revolution\Copilot\Types\Rpc\AgentsDiscoverRequest;

$result = Copilot::client()->rpc()->agents()->discover();
// $result->agents は AgentInfo の配列
$result = Copilot::client()->rpc()->agents()->discover(new AgentsDiscoverRequest(
    projectPaths: ['/path/to/project'],
    excludeHostAgents: true,
));

// エージェント作成可能ディレクトリを取得 (experimental)
use Revolution\Copilot\Types\Rpc\AgentsGetDiscoveryPathsRequest;

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
use Revolution\Copilot\Types\Rpc\InstructionsDiscoverRequest as ServerInstructionsDiscoverRequest;

$result = Copilot::client()->rpc()->instructions()->discover();
// $result->sources は InstructionSource の配列
$result = Copilot::client()->rpc()->instructions()->discover(
    new ServerInstructionsDiscoverRequest(projectPaths: ['/path/to/project'])
);

// インストラクション作成可能ファイル/ディレクトリを取得 (experimental)
use Revolution\Copilot\Types\Rpc\InstructionsGetDiscoveryPathsRequest;

$result = Copilot::client()->rpc()->instructions()->getDiscoveryPaths();
// $result->paths は InstructionDiscoveryPath の配列
// $result->paths[0]->path - ファイル/ディレクトリパス
// $result->paths[0]->location - 場所（user, repository, working-directory, plugin）
// $result->paths[0]->kind - 種別（file, directory）
// $result->paths[0]->preferredForCreation - 新規作成時の推奨か
$result = Copilot::client()->rpc()->instructions()->getDiscoveryPaths(
    new InstructionsGetDiscoveryPathsRequest(location: 'repository')
);
```

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

### メソッドリスト

```php
// model
$session->rpc()->model()->getCurrent();
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
$session->rpc()->model()->list();
$session->rpc()->model()->list(new ModelListRequest(skipCache: true)); // キャッシュをスキップ

// setModel()ヘルパーでも同様にreasoningEffortやmodelCapabilitiesを指定可能
$session->setModel('claude-opus-4.7', ReasoningEffort::HIGH);
$session->setModel('claude-opus-4.7', 'high'); // 文字列でも指定可能
$session->setModel('gpt-4', modelCapabilities: ['supports' => ['vision' => true]]); // 配列でも指定可能

// mode
$session->rpc()->mode()->get();
$session->rpc()->mode()->set(new ModeSetRequest(mode: 'plan'));

// name
$session->rpc()->name()->get();
$session->rpc()->name()->set(new NameSetRequest(name: 'My Session'));

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

// instructions (セッションのインストラクションソースを取得)
$result = $session->rpc()->instructions()->getSources();
// $result->sources - InstructionsSources の配列

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

// mcp (experimental: MCPサーバーの管理)
$session->rpc()->mcp()->list();
$session->rpc()->mcp()->enable(new McpEnableRequest(serverName: 'server-name'));
$session->rpc()->mcp()->disable(new McpDisableRequest(serverName: 'server-name'));
$session->rpc()->mcp()->reload();
// MCPサーバーが実行中かどうかを確認
use Revolution\Copilot\Types\Rpc\McpIsServerRunningRequest;

$result = $session->rpc()->mcp()->isServerRunning(new McpIsServerRunningRequest(serverName: 'my-server'));
// $result->running - サーバーが実行中かどうか

// MCPサーバーのツール一覧を取得
use Revolution\Copilot\Types\Rpc\McpListToolsRequest;

$result = $session->rpc()->mcp()->listTools(new McpListToolsRequest(serverName: 'my-server'));
// $result->tools - McpTools の配列（name, description）

// MCPサーバーを停止
use Revolution\Copilot\Types\Rpc\McpStopServerRequest;

$session->rpc()->mcp()->stopServer(new McpStopServerRequest(serverName: 'my-server'));
// MCP OAuthログイン（認証が必要なMCPサーバー向け）
$result = $session->rpc()->mcp()->login(new McpOauthLoginRequest(serverName: 'my-server'));
// $result->authorizationUrl - OAuthフローのURL（認証が必要な場合）

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
$session->rpc()->extensions()->reload();

// compaction → history に名前変更
$session->rpc()->history()->compact();
// 特定のイベント以降の履歴を切り詰める
$session->rpc()->history()->truncate(new HistoryTruncateRequest(
    eventId: 'evt-123', // このイベントとそれ以降のすべてのイベントが削除される
));

// tools (プロトコルv3+: external_tool.requestedイベントへの応答)
$session->rpc()->tools()->handlePendingToolCall(new HandlePendingToolCallRequest(
    requestId: '...',
    result: 'ツールの実行結果',
));
// セッションの現在のツールメタデータを取得（experimental）
$session->rpc()->tools()->getCurrentMetadata();
// サブエージェントの設定を更新（experimental）
use Revolution\Copilot\Types\Rpc\SubagentSettings;
use Revolution\Copilot\Types\Rpc\SubagentSettingsEntry;
use Revolution\Copilot\Types\Rpc\UpdateSubagentSettingsRequest;

$result = $session->rpc()->tools()->updateSubagentSettings(new UpdateSubagentSettingsRequest(
    settings: new SubagentSettings(entries: [
        new SubagentSettingsEntry(agentName: 'my-agent', contextTier: 'medium', enabled: true),
    ]),
));
// $result->settings - 更新後の SubagentSettings

// permissions (プロトコルv3+: permission.requestedイベントへの応答)
$session->rpc()->permissions()->handlePendingPermissionRequest(new PermissionDecisionRequest(
    requestId: '...',
    result: PermissionDecision::approveOnce(),
));
// セッション内のすべての権限リクエストを自動承認
$session->rpc()->permissions()->setApproveAll(new PermissionsSetApproveAllRequest(enabled: true));
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

// ui: 保留中のエリシテーションリクエストへの応答（elicitation.requestedイベント経由）
$session->rpc()->ui()->handlePendingElicitation(new UIHandlePendingElicitationRequest(
    requestId: '...',
    result: ['action' => 'accept', 'content' => ['name' => 'John']],
));

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
use Revolution\Copilot\Types\Rpc\ShellCancelUserRequestedRequest;

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

// queue: キューに積まれたユーザー向け項目の確認・管理
$pending = $session->rpc()->queue()->pendingItems();
// $pending->items - QueuePendingItems の配列
// $pending->items[0]->kind - QueuePendingItemsKind（Command / Message）

$removed = $session->rpc()->queue()->removeMostRecent();
// $removed->removed - 直近項目を削除できたかどうか

$session->rpc()->queue()->clear();

// schedule (experimental: スケジュール済みプロンプト管理)
$schedule = $session->rpc()->schedule()->list();
// $schedule->entries - ScheduleEntry の配列

use Revolution\Copilot\Types\Rpc\ScheduleStopRequest;

$stopped = $session->rpc()->schedule()->stop(new ScheduleStopRequest(id: 1));
// $stopped->stopped - 停止に成功したかどうか

// tasks (experimental: バックグラウンドエージェントタスク管理)
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
// $list->tasks - TaskAgentInfo または TaskShellInfo の配列

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

## SessionFS コールバック型

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

### SQLite コールバック型

| 型クラス | 用途 |
|---|---|
| `SessionFsSetProviderCapabilities` | プロバイダーの対応機能宣言（SQLiteサポートなど） |
| `SessionFsSqliteExistsRequest` / `SessionFsSqliteExistsResult` | SQLiteデータベースの存在確認 |
| `SessionFsSqliteQueryRequest` / `SessionFsSqliteQueryResult` | SQLiteクエリ実行 |

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
