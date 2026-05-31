# Plugin Directories

Plugin Directoryは、スキル、フック、MCPサーバー、カスタムエージェント、LSP設定などを1つのディレクトリにまとめて読み込む仕組みです。再利用可能な機能セットをアプリやリポジトリに同梱したい場合に使います。

Laravel版では`SessionConfig`または`ResumeSessionConfig`の`pluginDirectories`で指定できます。

## 使いどころ

- 複数の拡張機能を1つの能力パックとして配布する
- リポジトリにプラグインを同梱し、全員が同じ設定を使えるようにする
- Marketplace公開前のプラグインをローカルで開発・検証する
- インストール済みプラグインをローカルcheckoutで一時的に上書きする

MCPサーバー1つ、フック1つ、カスタムエージェント1つだけなら、`mcpServers`、`hooks`、`customAgents`へ直接指定するほうが単純です。Plugin Directoryは、関連する複数機能をまとめて配布したい場合に向いています。

## ディレクトリ構成

Copilot CLIは各プラグインディレクトリから`plugin.json`またはルート直下の`SKILL.md`を探します。

```text
my-plugin/
├── plugin.json
├── SKILL.md
├── hooks.json
├── .mcp.json
├── agents/
│   └── code-reviewer.md
└── skills/
    └── lint-fix/
        └── SKILL.md
```

`plugin.json`は`.github/plugin.json`や`.github/plugin/plugin.json`にも配置できます。スキル、フック、MCP、エージェントなどはそれぞれ独立したローダーを持つため、必要なものだけ含めれば十分です。

## Laravelから読み込む

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;

Copilot::start(function (CopilotSession $session): void {
    $response = $session->sendAndWait(
        prompt: 'この変更をプラグインのレビュー方針で確認して',
    );

    dump($response->content());
}, config: new SessionConfig(
    pluginDirectories: [
        base_path('plugins/code-reviewer'),
        base_path('plugins/lint-fix'),
    ],
));
```

配列形式でも指定できます。

```php
Copilot::run(
    prompt: 'READMEを改善して',
    config: [
        'pluginDirectories' => [
            base_path('plugins/docs-writer'),
        ],
    ],
);
```

## `cli_args`との違い

公式SDKではランタイム起動時の`--plugin-dir`引数として説明される場合があります。Laravel版ではセッション設定の`pluginDirectories`を使うのが基本です。

一方で、CLIプロセス起動そのものに引数を渡したい場合は`config/copilot.php`の`cli_args`または`Copilot::useStdio()`の`cli_args`を使えます。ただし`cli_args`はstdioでSDKがCLIを起動する場合だけ有効で、`useTcp()`など外部runtime接続時は無視されます。

```php
$stdioConfig = config('copilot');
$stdioConfig['cli_args'] = [
    '--plugin-dir',
    base_path('plugins/code-reviewer'),
];

Copilot::useStdio($stdioConfig)->start(fn (CopilotSession $session) => ...);
```

通常は`pluginDirectories`を優先してください。セッション単位で明示でき、他の`SessionConfig`設定と同じ場所で管理できます。

## 再現性のための運用

- 相対パスより`base_path()`などで絶対パス化する
- リポジトリに同梱するプラグインはバージョン管理する
- Marketplaceや外部配布のプラグインを使う場合は、利用バージョンをドキュメント化する
- 本番環境では、ユーザー入力から任意のプラグインパスを直接指定させない

## 関連ドキュメント

- [SessionConfig](./session-config.md)
- [Custom Agents](./custom-agents.md)
- [MCP](./mcp.md)
- [Hooks](./hooks.md)
- [Skills](./skills.md)
