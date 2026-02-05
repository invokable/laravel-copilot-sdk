# SessionConfig

`SessionConfig`クラスで様々な設定が可能。

`Copilot::run(prompt: '...', config: $config)`や`Copilot::start(function (CopilotSession $session) { ... }, config: $config)`のように使用する。  
単純にモデルを指定したいだけのような`SessionConfig`クラスを使うまでもない時は配列での指定も可能。`Copilot::run(prompt: '...', config: ['model' => 'claude-opus-4.5'])`

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SystemMessageConfig;
use Revolution\Copilot\Types\InfiniteSessionConfig;
use Revolution\Copilot\Types\UserInputRequest;
use Revolution\Copilot\Enums\ReasoningEffort;

$config = new SessionConfig(
    // 使用するモデルの指定
    model: 'claude-opus-4.5',

    // 新規セッション作成時に固定のセッションIDを指定
    sessionId: 'session-123',

    // 推論レベル。基本的にOpenAIの対応しているモデルのみ設定可能。
    reasoningEffort: ReasoningEffort::HIGH,

    // configディレクトリを上書き設定
    configDir: '',

    // カスタムツール
    tools: [...],

    // システムメッセージ
    systemMessage: new SystemMessageConfig(
        content: 'You are a helpful assistant for Laravel developers.',
    ),

    // カスタムプロバイダー
    provider: new ProviderConfig(),

    onPermissionRequest: function (array $request) {
        // 権限リクエストを処理
    },

    onUserInputRequest: function (UserInputRequest $request) {
        // ユーザーインプットリクエストを処理。ask_user
    },

    // セッションフック
    hooks: [],

    // ワーキングディレクトリ
    workingDirectory: '',

    // ストリーミングを有効化
    streaming: true,

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

    // スキルディレクトリ
    skillDirectories: [],

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
);

$response = Copilot::run('...', config: $config);
```

セッション再開時には`ResumeSessionConfig`クラスを使用する。`SessionConfig`とほとんど同じだけど少しだけ違う。ResumeSessionConfigは設定を変えたい項目のみ指定。他は新規セッション開始時の設定が引き継がれる。
