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
Copilot::client()->rpc()->mcp()->update(new McpConfigUpdateRequest(
    name: 'my-server',
    config: new McpServerValue(type: 'http', url: 'https://mcp.example.com'),
));
Copilot::client()->rpc()->mcp()->remove(new McpConfigRemoveRequest(name: 'my-server'));
// MCPサーバーを有効化/無効化（グローバル設定）
Copilot::client()->rpc()->mcp()->enable(new McpConfigEnableRequest(names: ['my-server']));
Copilot::client()->rpc()->mcp()->disable(new McpConfigDisableRequest(names: ['my-server']));

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
// modelCapabilitiesをオーバーライドする場合
$session->rpc()->model()->switchTo(new ModelSwitchToRequest(
    modelId: 'gpt-4',
    modelCapabilities: new ModelCapabilitiesOverride(
        supports: new ModelCapabilitiesOverrideSupports(vision: true),
    ),
));

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

// workspaces
$session->rpc()->workspaces()->getWorkspace();
$session->rpc()->workspaces()->listFiles();
$session->rpc()->workspaces()->readFile(new WorkspacesReadFileRequest(path: 'file.txt'));
$session->rpc()->workspaces()->createFile(new WorkspacesCreateFileRequest(path: 'file.txt', content: '...'));

// instructions (セッションのインストラクションソースを取得)
$result = $session->rpc()->instructions()->getSources();
// $result->sources - InstructionsSources の配列

// fleet
$session->rpc()->fleet()->start(new FleetStartRequest(prompt: '...'));

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
// MCP OAuthログイン（認証が必要なMCPサーバー向け）
$result = $session->rpc()->mcp()->login(new McpOauthLoginRequest(serverName: 'my-server'));
// $result->authorizationUrl - OAuthフローのURL（認証が必要な場合）

// plugins (experimental: プラグインの一覧)
$session->rpc()->plugins()->list();

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
$session->rpc()->tools()->handlePendingToolCall(new ToolsHandlePendingToolCallRequest(
    requestId: '...',
    result: 'ツールの実行結果',
));

// permissions (プロトコルv3+: permission.requestedイベントへの応答)
$session->rpc()->permissions()->handlePendingPermissionRequest(new PermissionDecisionRequest(
    requestId: '...',
    result: PermissionRequestResultKind::approved(),
));
// セッション内のすべての権限リクエストを自動承認
$session->rpc()->permissions()->setApproveAll(new PermissionsSetApproveAllRequest(enabled: true));
// セッションスコープの権限承認をリセット
$session->rpc()->permissions()->resetSessionApprovals();

// commands: コマンド呼び出しイベントへの応答
$session->rpc()->commands()->handlePendingCommand(new CommandsHandlePendingCommandRequest(
    requestId: '...',
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
```

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

これらの型クラスは`src/Types/Rpc/`に配置されています。

## 配列での引数指定

引数も返り値も専用のクラスを使っていますが引数は配列で指定することも可能です。

```php
$session->rpc()->mode()->set(['mode' => 'plan']);
```

## Testing

`Copilot::fake()`でのモックは使えないので`Copilot::expects('client')`や`Copilot::expects('start')`でモックしてください。
