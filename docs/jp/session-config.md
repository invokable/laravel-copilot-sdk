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
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverride;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverrideSupports;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverrideLimits;
use Revolution\Copilot\Enums\ReasoningEffort;

$config = new SessionConfig(
    // 使用するモデルの指定
    model: 'auto',

    // 新規セッション作成時に固定のセッションIDを指定
    sessionId: 'session-123',

    clientName: 'my-app',

    // 推論レベル。対応しているモデルでのみ設定可能。
    reasoningEffort: ReasoningEffort::HIGH,

    // モデルのcapabilitiesをオーバーライド。ランタイムのデフォルトにdeep-mergeされる。
    // modelCapabilities: new ModelCapabilitiesOverride(
    //     supports: new ModelCapabilitiesOverrideSupports(vision: true, reasoningEffort: true),
    //     limits: new ModelCapabilitiesOverrideLimits(max_prompt_tokens: 200000),
    // ),
    // 配列での指定も可能
    // modelCapabilities: ['supports' => ['vision' => true]],

    // configディレクトリを上書き設定
    configDirectory: '',

    // MCP設定やスキルディレクトリの自動検出を有効化
    // .mcp.json や .vscode/mcp.json をワーキングディレクトリから自動検出し、
    // 明示的に指定した mcpServers や skillDirectories とマージする（名前衝突時は明示指定が優先）
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

    // MCPサーバー設定
    mcpServers: [
        'github' => [
            'type' => 'http',
            'url' => 'https://api.githubcopilot.com/mcp/',
        ],
    ],

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

    // スキルディレクトリ
    skillDirectories: [],

    // Plugin Directory。スキル、フック、MCP、カスタムエージェントなどをまとめて読み込む
    pluginDirectories: [],

    // カスタム指示ファイルを検索する追加ディレクトリ
    instructionDirectories: [],

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

    // **実験的機能** Canvas runtime support (v1.0.0-beta.7+)
    // このセッション参加者が提供するキャンバス。宣言した接続がキャンバス操作のライブプロバイダーになる。
    // canvases: [...],

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

セッション再開時には`ResumeSessionConfig`クラスを使用します。`SessionConfig`とほとんど同じですが少しだけ違います。ResumeSessionConfigは設定を変えたい項目のみ指定します。他は新規セッション開始時の設定が引き継がれます。

`ResumeSessionConfig`特有のフィールドとして`openCanvases`があります。セッションが中断された時に既に開いていたキャンバスのスナップショットを提供すると、ランタイムはキャンバスの状態を再ハイドレートできるため、以前のシャットダウン前にアクティブだったキャンバスを再度開く必要がありません。（実験的機能）

カスタムエージェントの使い方は [Custom Agents](./custom-agents.md) を参照。
クラウド実行は [Cloud Sessions](./cloud-sessions.md)、Plugin Directoryは [Plugin Directories](./plugin-directories.md) を参照。
