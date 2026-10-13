# SessionConfig

`SessionConfig`クラスで様々な設定が可能です。

`Copilot::run(prompt: '...', config: $config)`や`Copilot::start(function (CopilotSession $session) { ... }, config: $config)`のように使用します。  
単純にモデルを指定したいだけのような`SessionConfig`クラスを使うまでもない時は配列での指定も可能です。`Copilot::run(prompt: '...', config: ['model' => 'auto'])`

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SystemMessageConfig;
use Revolution\Copilot\Types\InfiniteSessionConfig;
use Revolution\Copilot\Types\UserInputRequest;
use Revolution\Copilot\Types\McpAuthRequest;
use Revolution\Copilot\Types\McpAuthToken;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverride;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverrideSupports;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverrideLimits;
use Revolution\Copilot\Types\Rpc\CanvasAction;
use Revolution\Copilot\Types\Rpc\CanvasProviderInvokeActionRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderOpenRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderOpenResult;
use Revolution\Copilot\Types\CanvasDeclaration;
use Revolution\Copilot\Support\CanvasError;
use Revolution\Copilot\Support\CanvasHandler;
use Revolution\Copilot\Enums\ReasoningEffort;

$config = new SessionConfig(
    // 使用するモデルの指定
    model: 'auto',

    // 新規セッション作成時に固定のセッションIDを指定
    sessionId: 'session-123',

    // ローカルセッションの作成時に名前を設定（cloud と併用するとランタイムが拒否）
    // 名前付きセッションは最初から永続化され、turn実行前でも一覧・再開できる。
    // 空文字・前後空白・制御文字・ダブルクォート不可、UTF-16コード単位で100以内。
    name: 'issue-triage',

    clientName: 'my-app',

    // 推論レベル。対応しているモデルでのみ設定可能。
    reasoningEffort: ReasoningEffort::HIGH,

    // 実験的機能を有効化（未指定時はランタイムの既定値）
    enableExperimentalMode: true,

    // モデルのcapabilitiesをオーバーライド。ランタイムのデフォルトにdeep-mergeされる。
    // modelCapabilities: new ModelCapabilitiesOverride(
    //     supports: new ModelCapabilitiesOverrideSupports(vision: true, reasoningEffort: true),
    //     limits: new ModelCapabilitiesOverrideLimits(max_prompt_tokens: 200000),
    // ),
    // 配列での指定も可能
    // modelCapabilities: ['supports' => ['vision' => true]],

    // configディレクトリを上書き設定
    configDirectory: '',

    // MCP設定とスキルディレクトリの自動検出を有効化
    // .mcp.json や .vscode/mcp.json、スキルディレクトリをワーキングディレクトリから検出し、
    // 明示的に指定した mcpServers や skillDirectories とマージする（MCP名の衝突時は明示指定が優先）
    enableConfigDiscovery: true,

    // カスタムツール
    tools: [...],

    // システムメッセージ
    systemMessage: new SystemMessageConfig(
        content: 'You are a helpful assistant for Laravel developers.',
    ),

    // システムメッセージ: replaceモード（システムプロンプトを完全に置き換え）
    // systemMessage: new SystemMessageConfig(
    //     mode: 'replace',
    //     content: 'カスタムシステムプロンプト全体',
    // ),

    // システムメッセージ: customizeモード（セクション単位でオーバーライド）
    // systemMessage: new SystemMessageConfig(
    //     mode: 'customize',
    //     sections: [
    //         'tone' => new SectionOverride(action: SectionOverrideAction::REPLACE, content: 'Always respond in Japanese.'),
    //         'safety' => new SectionOverride(action: SectionOverrideAction::REMOVE),
    //         'custom_instructions' => new SectionOverride(action: SectionOverrideAction::APPEND, content: 'Additional instructions here.'),
    //     ],
    //     content: 'appendモード同様の追加コンテンツ（オプション）',
    // ),

    // カスタムプロバイダー
    provider: new ProviderConfig(),

    // マルチプロバイダー BYOK（Bring Your Own Key）設定 (experimental)
    // 名前付きプロバイダー接続のリスト。provider と同時使用不可。
    providers: [
        [
            'id' => 'my-openai',
            'type' => 'openai',
            'apiKey' => env('OPENAI_API_KEY'),
            'apiUrl' => 'https://api.openai.com/v1',
        ],
        [
            'id' => 'my-anthropic',
            'type' => 'anthropic',
            'apiKey' => env('ANTHROPIC_API_KEY'),
        ],
    ],

    // BYOK モデル定義 (experimental)
    // providers で定義したプロバイダーを参照するモデルリスト。
    models: [
        ['id' => 'gpt-5', 'providerId' => 'my-openai', 'modelId' => 'gpt-5-latest'],
        ['id' => 'claude-4', 'providerId' => 'my-anthropic', 'modelId' => 'claude-opus-4'],
    ],

    // メモリ設定 (experimental)
    // セッション間でのメモリ機能を制御する。
    memory: new \Revolution\Copilot\Types\MemoryConfiguration(
        enabled: true,
    ),
    // 配列での指定も可能
    // memory: ['enabled' => true],

    // これらのBYOK・メモリ設定は、作成時と再開時のRPCペイロードに送信される。
    // 再開時はResumeSessionConfigまたは同じキーを持つ配列で指定する。

    // セッションごとのGitHubトークン（マルチテナント対応）
    // クライアントレベルのgithub_tokenとは別に、セッション単位でトークンを指定できる
    gitHubToken: $user->github_token,

    // リモートセッションの動作モード
    // 'off' - ローカルのみ（デフォルト）
    // 'export' - セッションイベントをGitHubにエクスポート（リモートステアリングなし）
    // 'on' - エクスポートとリモートステアリングを両方有効化
    remoteSession: \Revolution\Copilot\Enums\RemoteSessionMode::Export,

    // クラウドセッション（ローカルではなくクラウドでセッションを作成）
    // オプションでリポジトリ情報を関連付けられる
    cloud: new \Revolution\Copilot\Types\CloudSessionOptions(
        repository: new \Revolution\Copilot\Types\CloudSessionRepository(
            owner: 'myorg',
            name: 'myrepo',
            branch: 'main',
        ),
    ),

    onPermissionRequest: function (array $request) {
        // 権限リクエストを処理
    },

    onUserInputRequest: function (UserInputRequest $request) {
        // ユーザーインプットリクエストを処理。ask_user
    },

    // エリシテーションリクエストのハンドラー
    // 設定するとエージェントからのフォームベースUIダイアログリクエストを受け取れる
    onElicitationRequest: function (ElicitationContext $context) {
        // エリシテーションリクエストを処理
        return ['action' => 'accept', 'content' => ['field' => 'value']];
    },

    // MCPサーバーがOAuth認証を要求したときにホスト側でトークンを取得
    onMcpAuthRequest: function (McpAuthRequest $request, array $context): ?McpAuthToken {
        $accessToken = $credentialProvider->tokenFor($request->serverName);

        return $accessToken === null
            ? null
            : new McpAuthToken(accessToken: $accessToken, tokenType: 'Bearer');
    },

    // プランモード終了リクエストのハンドラー
    // 設定するとエージェントがプランモードを終了する際のリクエストを受け取れる
    onExitPlanModeRequest: function (ExitPlanModeRequest $request) {
        // プラン内容を確認して承認・却下
        return new ExitPlanModeResult(approved: true, selectedAction: $request->recommendedAction);
    },

    // 自動モード切替リクエストのハンドラー
    // レートリミット到達時にautoモードへの切替を許可するか選択できる
    onAutoModeSwitchRequest: function (AutoModeSwitchRequest $request) {
        // "yes", "yes_always", "no" のいずれかを返す
        return 'yes';
    },

    // セッション内部テレメトリの有効/無効化（デフォルト: null = 有効）
    // falseに設定するとこのセッションのテレメトリを無効化
    // カスタムプロバイダー（BYOK）設定時は常に無効
    enableSessionTelemetry: true,

    // セッションフック
    hooks: [],

    // ワーキングディレクトリ
    workingDirectory: '',

    // ストリーミングを有効化
    streaming: true,

    // サブエージェントのストリーミングイベントをメインストリームに含めるか (デフォルト: true)
    includeSubAgentStreamingEvents: true,

    // 使用可能なビルトインツール
    availableTools: ['read_file', 'write_file'],

    // もしくは除外するツール
    excludedTools: ['shell'],

    // 除外するビルトインエージェント（v1.0.5+）
    // 指定されたビルトインエージェントはセッションから非表示になり、同名のカスタムエージェントがない限り選択・呼び出しできない
    excludedBuiltinAgents: ['coding'],

    // セッションのAIクレジット上限（実験的機能）
    sessionLimits: new SessionLimitsConfig(maxAiCredits: 100.0),

    // MCPサーバー設定
    mcpServers: [
        'github' => [
            'type' => 'http',
            'url' => 'https://api.githubcopilot.com/mcp/',
        ],
    ],

    // セッションで無効にするMCPサーバー名
    disabledMcpServers: ['github'],

    // ファイル変更履歴を有効化（rewind / 累積diff用）
    enableFileChangeTracking: true,

    // ホストから注入するManaged Settings（権限制御）
    managedSettings: [
        'permissions' => [
            'deny' => ['Shell(rm *)'],
            'disableAssistedPermissionsMode' => true,
            'limitTo' => ['Domain(example.com)'],
        ],
    ],

    // Managed Settingsのロックされたmodel / Auto tier / reasoning effort / context tierへの変更を拒否
    enforceManagedModelDefaults: true,

    // カスタムエージェント
    customAgents: [
        [
            'name' => 'reviewer',
            'displayName' => 'Code Reviewer',
            'description' => 'Reviews code for best practices',
            'prompt' => 'You are an expert code reviewer.',
        ],
    ],

    // デフォルトエージェントの設定。カスタムエージェントが選択されていない場合に使用されるビルトインエージェントの設定。
    // excludedTools: デフォルトエージェントから除外するツールのリスト。
    // カスタムサブエージェントには引き続き利用可能。
    defaultAgent: ['excludedTools' => ['tool_name']],

    // セッション開始時にアクティブにするエージェントを指定。customAgentsのnameと一致する必要がある。
    agent: 'reviewer',

    // スキル・エージェント・フック・指示ファイルの明示的な検出ルート
    skillDirectories: [],
    agentDirectories: [],
    hookDirectories: [],
    instructionDirectories: [],

    // Plugin Directory。スキル、フック、MCP、カスタムエージェントなどをまとめて読み込む
    pluginDirectories: [],

    // ワーキングディレクトリ以外にエージェントがファイルアクセスできる追加ディレクトリ
    additionalDirectories: ['/path/to/shared'],

    // 無効なスキル
    disabledSkills: [],

    // 無限セッション設定。デフォルトは有効。
    infiniteSessions: new InfiniteSessionConfig(
        enabled: true,
        backgroundCompactionThreshold: 0.80, // コンテキスト使用率が80%になったら圧縮を開始する
        bufferExhaustionThreshold: 0.95,     // 圧縮が完了するまで95%でブロックする
    ),

    // 無限セッションを無効化
    // infiniteSessions: new InfiniteSessionConfig(enabled: false),

    // セッション作成RPCより前にイベントハンドラを登録する。
    // session.start などの早期イベントを取りこぼさないようにするために使用する。
    // $session->on($handler) を後から呼ぶのと同等だが、ライフサイクルのより早い段階で登録される。
    onEvent: function (SessionEvent $event) {
        // すべてのセッションイベントを受け取る
    },

    // **実験的機能** キャンバスの宣言と provider ハンドラー
    canvases: [
        new CanvasDeclaration(
            id: 'editor',
            displayName: 'Editor',
            description: 'Opens and edits project files.',
            actions: [
                new CanvasAction(name: 'format', description: 'Format the open file.'),
            ],
        ),
    ],
    canvasHandler: new class extends CanvasHandler
    {
        public function open(CanvasProviderOpenRequest $request): CanvasProviderOpenResult|array
        {
            return new CanvasProviderOpenResult(
                url: 'https://app.example.test/canvas/'.$request->instanceId,
                title: 'Project editor',
            );
        }

        public function invoke(CanvasProviderInvokeActionRequest $request): mixed
        {
            if ($request->actionName !== 'format') {
                throw CanvasError::noHandler();
            }

            return ['formatted' => true];
        }
    },
    requestCanvasRenderer: true,

    // **実験的機能** レンダラー側のオプトイン: trueの場合、ランタイムはこの接続用のキャンバスエージェントツールをモデルに提供する。
    // キャンバスを表示できないSDK呼び出し元がクリーンな状態を保つため、デフォルトはoff。
    // requestCanvasRenderer: false,

    // **実験的機能** 拡張機能サーフェスのオプトイン: trueの場合、ランタイムは拡張機能管理ツールをこの接続のセッションに接続する。
    // 拡張機能を公開しない呼び出し元がクリーンな状態を保つため、デフォルトはoff。
    // requestExtensions: false,

    // **実験的機能** この接続上のキャンバスプロバイダーのための安定した拡張機能ID。
    // 設定すると、ランタイムは再接続固有の接続IDの代わりに`${source}:${name}`をエージェント向け拡張機能IDとして使用する。
    // extensionInfo: new ExtensionInfo(source: 'github-app', name: 'my-extension'),
    // 配列での指定も可能
    // extensionInfo: ['source' => 'github-app', 'name' => 'my-extension'],
);

$response = Copilot::run('...', config: $config);
```

`skillDirectories`、`agentDirectories`、`hookDirectories`、`instructionDirectories` は、それぞれスキル・カスタムエージェント・ファイルフック・カスタム指示の**検出専用ルート**です。これらを指定しても、エージェントに一般的なファイルアクセス権限は付与されません。通常のファイルアクセス先を追加する場合は `additionalDirectories` を使います。

検出ルートは `enableConfigDiscovery` の有効・無効にかかわらず指定できます。相対パスは指定時の有効な `workingDirectory` を基準に一度だけ解決され、後から作業ディレクトリが変わってもルートは移動しません。省略または `null` は既定の検出元を変更せず、`[]` はSDKが明示したルートだけをクリアします。スキル・エージェント・フック・指示ファイルそれぞれのソース無効化設定や Managed Settings は引き続き適用されます。

`limitTo`はホスト単位のegress許可リストです。`Domain(hostname)`、`Domain(IP)`、`Domain(*.example.com)`形式を使い、schemeやport、pathは指定できません。`disableAssistedPermissionsMode: true`はAssisted Permissionsの有効化を禁止します。`enforceManagedModelDefaults`を有効にすると、上書き不可のManaged Settingsと競合するモデル設定の変更がエラーになります。

モデルのManaged Settingsでは`model`、`autoTier`、`contextTier`、`effortLevel`を指定できます。ランタイムが値を利用可能な設定へ調整した場合、Managed Settingsのメタデータに元の値`requested`が含まれます。

セッション再開時には`ResumeSessionConfig`クラスを使用します。`SessionConfig`とほとんど同じですが少しだけ違います。ResumeSessionConfigは設定を変えたい項目のみ指定します。他は新規セッション開始時の設定が引き継がれます。

`ResumeSessionConfig`特有のフィールドとして`openCanvases`があります。セッションが中断された時に既に開いていたキャンバスのスナップショットを提供すると、ランタイムはキャンバスの状態を再ハイドレートできるため、以前のシャットダウン前にアクティブだったキャンバスを再度開く必要がありません。（実験的機能）

Canvas を再開する場合も、`canvases` と `canvasHandler` を再度指定します。`canvasHandler` は PHP プロセス内でのみ使用され、RPC payload には宣言メタデータだけが送られます。開いている状態は `$session->openCanvases()` で取得でき、`session.canvas.opened` / `session.canvas.closed` イベントに合わせて更新されます。

カスタムエージェントの使い方は [Custom Agents](./custom-agents.md) を参照。
クラウド実行は [Cloud Sessions](./cloud-sessions.md)、Plugin Directoryは [Plugin Directories](./plugin-directories.md) を参照。

## セッションごとのGitHubトークンプロバイダー

複数のユーザーにサービスを提供するアプリケーションでは、セッション設定にトークンを保存せず、短期間有効なトークンを返すコールバックを指定できます。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\SessionConfig;

$session = Copilot::client()->createSession(new SessionConfig(
    onPermissionRequest: PermissionHandler::approveAll(),
    gitHubTokenProvider: fn (array $request) => [
        'kind' => 'token',
        'accessToken' => auth()->user()->github_token,
        'expiresIn' => 3600,
    ],
));
```

コールバックには`host`、`sessionId`、`reason`（`initial`または`refresh`）が渡されます。
`gitHubToken`と`gitHubTokenProvider`は同時に指定できません。

## SessionFsプロバイダー

クライアントオプション`sessionFs`（`session_fs`でも可）を指定すると、Copilot CLIのセッション状態（session-stateファイルやSQLite）を独自のストレージへ委譲できます。この場合、すべてのセッション設定で`createSessionFsProvider`が必須です。`createSession()`と`resumeSession()`の両方で、セッションのIDが確定した後・ランタイムへのリクエスト送信前に呼び出されます。

```php
use Revolution\Copilot\Client;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\SessionConfig;

$client = new Client(['sessionFs' => [
    'initialCwd' => '/workspace',
    'sessionStatePath' => '/session-state',
    'conventions' => 'posix', // または windows
    'capabilities' => ['sqlite' => true, 'binary' => true], // 任意
]]);

$session = $client->createSession(new SessionConfig(
    onPermissionRequest: PermissionHandler::approveAll(),
    createSessionFsProvider: fn (CopilotSession $session) => new MySessionFsProvider($session->id()),
));
```

`createSessionFsProvider`は`Closure`で、`SessionConfig`と`ResumeSessionConfig`の配列/`fromArray()`/`toArray()`でも使えます。`capabilities.sqlite`を指定した場合はプロバイダーが`SessionFsSqliteCapableProvider`（または配列の`sqlite`キー）、`capabilities.binary`を指定した場合は`SessionFsBinaryProvider`（または`readFileBytes`/`writeFileBytes`）を実装している必要があります。満たさない場合は`InvalidArgumentException`になります。

クライアントオプション`sessionFs`が無い場合、`createSessionFsProvider`は呼び出されず、従来の`sessionFsProvider`（`readFile`/`writeFile`のみ）がそのまま使えます。詳細は[Getting Started](../getting-started.md)を参照してください。

## 高度なセッションオプション

組み込みの`ask_user`ツールでは構造化エリシテーション形式を使用できます。また、CAPI tierを指定してモデルの自動選択を調整できます。

```php
use Revolution\Copilot\Enums\AskUserVariant;
use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Types\CapiSessionOptions;
use Revolution\Copilot\Types\Rpc\EnqueueCommandParams;
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    askUserVariant: AskUserVariant::ELICITATION,
    capi: new CapiSessionOptions(autoTier: AutoTier::BALANCE),
);
```

## マルチテナント向け empty モード

クライアント設定の`mode`を`empty`にすると、Copilot CLI のホスト環境やセッション間状態に依存する機能を既定で無効にします。省略時は従来どおり`copilot-cli`です。empty モードでは永続化先として`base_directory`、`session_fs`、または外部サーバーの`url`のいずれかを指定してください。

```php
// config/copilot.php
'mode' => env('COPILOT_MODE', 'copilot-cli'),
'base_directory' => storage_path('app/copilot'),
```

empty モードでは各セッションに`availableTools`の指定が必要です。安全な組み込みツールだけを許可するには`BuiltInTools::Isolated`を使います。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\BuiltInTools;
use Revolution\Copilot\Support\ToolSet;

$response = Copilot::run(
    prompt: 'Summarize this text.',
    config: [
        'availableTools' => (new ToolSet)->addBuiltIn(BuiltInTools::Isolated),
    ],
);
```

empty モードではセッションテレメトリー、永続 MCP OAuth token storage、embedding retrieval/cache、instruction discovery、file hooks、host git operations、session store、skills、memory、experimental mode が安全側の既定値になります。明示したセッション設定はこれらの既定値より優先されます。`systemMessage`の`replace`はそのまま利用でき、それ以外のモードでは`environment_context`セクションを除去します。CLI プロセスでは`COPILOT_DISABLE_KEYTAR=1`を設定します。

`AskUserVariant::ELICITATION`は`onElicitationRequest`ハンドラーと組み合わせて使用します。デフォルトのvariantは引き続き`legacy`です。

MCP認可のホストIDを識別させたい場合は`authClientIdMetadataUrl`にOAuth Client ID Metadata DocumentのURLを指定します。未設定の場合はホストIDが渡されません。

```php
$config = new SessionConfig(
    authClientIdMetadataUrl: 'https://example.com/client-metadata.json',
);
```

### MCP Apps

MCP Appsを有効にするには`enableMcpApps: true`を指定します。Laravel側の設定名は`enableMcpApps`のままですが、ランタイムには`requestMcpApps: true`として送信されます。従来の誤ったワイヤキーを修正したため、この設定がランタイムで有効になります。`false`または未指定の場合は送信されません。セッション再開時も`ResumeSessionConfig`または配列で同じ設定を指定できます。

```php
$config = new SessionConfig(enableMcpApps: true);
```

セッション単位の実験的なRPCは`$session->rpc()`から利用できます。

```php
$session->rpc()->commands()->enqueue(new EnqueueCommandParams('/compact'));
$sandbox = $session->rpc()->sandbox()->getEnforcementStatus();
```

## MCP診断とサンドボックスのsession設定

MCP診断を有効にするには`diagnostics`を指定します。`debug`や`trace`の記録にはツール引数、MCP payload、パス、サーバーstderrが含まれる場合があるため、ログを外部送信する前に内容を確認してください。省略時は診断記録を有効にしません。

```php
$config = new SessionConfig(
    diagnostics: ['sources' => ['mcp' => ['level' => 'debug']]],
    sandbox: [
        'enabled' => true,
        'userPolicy' => [
            'filesystem' => ['readwritePaths' => [storage_path('app/workspace')]],
            'network' => [
                'allowedHosts' => ['api.example.com'],
                'blockedHosts' => ['untrusted.example.com'],
            ],
        ],
    ],
);
```

`sandbox`は`SandboxConfig`、`SandboxConfigUserPolicyFilesystem`、`SandboxConfigUserPolicyNetwork`で型指定もできます。ネットワークの許可/拒否ルールはCLIのサンドボックス機能とバージョンに依存します。`managedMcpServers`では安定したキーごとに`ManagedMcpServerConfig`を渡せます。

```php
use Revolution\Copilot\Types\Rpc\ManagedMcpServerConfig;
use Revolution\Copilot\Types\Rpc\SandboxConfig;
use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicy;
use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicyNetwork;

$config = new SessionConfig(
    managedMcpServers: [
        'docs' => new ManagedMcpServerConfig(
            displayName: 'Documentation',
            url: 'https://api.example.com/mcp',
            tools: ['search'],
        ),
    ],
    sandbox: new SandboxConfig(
        enabled: true,
        userPolicy: new SandboxConfigUserPolicy(
            network: new SandboxConfigUserPolicyNetwork(
                allowedHosts: ['api.example.com'],
            ),
        ),
    ),
);
```
