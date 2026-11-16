# クライアントオプション一覧

クライアントの動作は `config/copilot.php` で設定します。同じオプションは `Copilot::client()` や `useStdio()` / `useTcp()`、`new Client([...])` に配列で渡すこともできます。このページは設定ファイルにあるオプションと、設定ファイルにはないがコードから渡せるオプションの一覧です。

## 設定ファイルのオプション

| オプション | デフォルト | 環境変数 | TCPモード |
| --- | --- | --- | --- |
| `mode` | `'copilot-cli'` | `COPILOT_MODE` | 共通 |
| `url` | `null` | `COPILOT_URL` | 設定するとTCPモードになる |
| `cli_path` | `'copilot'` | `COPILOT_CLI_PATH` | 無視される |
| `cli_args` | `[]` | - | 無視される |
| `cwd` | `null`（アプリのベースパス） | - | 無視される |
| `log_level` | `'info'` | `COPILOT_LOG_LEVEL` | 無視される |
| `builtin_plugin_directories` | `[]` | - | 共通 |
| `timeout` | `60.0` | `COPILOT_TIMEOUT` | 共通 |
| `model` | `null` | `COPILOT_MODEL` | 共通 |
| `copilot_home` | `null` | `COPILOT_HOME` | 無視される |
| `base_directory` | `null` | `COPILOT_BASE_DIRECTORY` | 無視される |
| `session_fs` | `null` | - | 共通 |
| `permission_approve` | `'deny-all'` | `COPILOT_PERMISSION_APPROVE` | 共通（Facade層） |
| `telemetry` | `null` | - | 無視される |
| `session_idle_timeout_seconds` | `0` | `COPILOT_SESSION_IDLE_TIMEOUT` | 無視される |
| `request_handler` | `null` | - | 共通 |

### 接続まわり

- `mode`: クライアントモード。`'copilot-cli'`（通常）か `'empty'`。`empty` はCLIの暗黙的な機能を無効化するモードで、ベースディレクトリ・SessionFs・TCP URL のいずれかを明示する必要があります。
- `url`: 既存のCopilot CLIサーバーのURL（例: `tcp://127.0.0.1:12345`）。設定するとSDKは新しいCLIプロセスを起動せず、このサーバーに接続します。詳しくは [TCPモード](tcp-mode.md) を参照してください。
- `cli_path`: CLI実行ファイルのパス。`null` を指定すると自動で探します。
- `cli_args`: CLI起動時に渡す追加の引数（例: `['--yolo']`）。
- `cwd`: CLIプロセスの作業ディレクトリ。
- `log_level`: CLIサーバーのログレベル。`none` / `error` / `warning` / `info` / `debug` / `all` から選びます。

### 既定値として使われるもの

- `timeout`: `sendAndWait()` のデフォルトのタイムアウト（秒）。`Copilot::run()` でも使われます。
- `model`: セッション作成時にモデルを指定しなかった場合のデフォルトモデル。
- `permission_approve`: Facade（`Copilot::run()` など）利用時の権限リクエストの自動処理。`'deny-all'`（すべて拒否）/ `'approve-safety'`（シェルと書き込み以外を承認）/ `'approve-all'`（すべて承認）/ `false`（ハンドラーを注入しない）から選びます。Clientを直接使う場合は常に `onPermissionRequest` ハンドラーが必須です。詳しくは [Permission Requests](permission-request.md) を参照してください。

### データの置き場所

- `copilot_home`: Copilotのデータ（セッション状態や設定）のベースディレクトリ。起動するCLIプロセスの `COPILOT_HOME` 環境変数として渡されます。未設定の場合はCLIのデフォルト（`~/.copilot`）です。
- `base_directory`: `copilot_home` と同じものを指す公式SDK名のエイリアス。両方を指定した場合は `base_directory` が優先されます。
- `session_fs`: セッション状態（ファイルとSQLite）を自前のストレージへ委譲するSessionFsプロバイダーの設定。`initialCwd` / `sessionStatePath` / `conventions` が必須で、設定した場合は各セッションでも `createSessionFsProvider` の指定が必要です。
- `builtin_plugin_directories`: 信頼済みの組み込みプラグインディレクトリの絶対パスの配列。クライアント起動時にランタイムへ登録されます。詳しくは [プラグインディレクトリ](plugin-directories.md) を参照してください。

### その他

- `telemetry`: OpenTelemetryの設定配列（`otlpEndpoint` / `filePath` / `exporterType` / `sourceName` / `captureContent`）。起動するCLIプロセスの環境変数に変換されます。詳しくは [OpenTelemetry](telemetry.md) を参照してください。
- `session_idle_timeout_seconds`: セッションのサーバー全体のアイドルタイムアウト（秒）。`0`（デフォルト）で無効になり、セッションは無期限に残ります。
- `request_handler`: モデル層へのHTTPリクエストを横取りする `CopilotRequestHandler` またはクロージャ（実験的機能）。WebSocketリクエストには使えません。

## 設定ファイルにないオプション

以下は設定ファイルには含まれていませんが、`useStdio()` や `Copilot::client()`、`new Client([...])` の引数として渡せます。

| オプション | 内容 | TCPモード |
| --- | --- | --- |
| `env` | 起動するCLIプロセスに追加で渡す環境変数の配列 | 無視される |
| `github_token` | 認証に使うGitHubトークン | 使えない |
| `use_logged_in_user` | CLIにログイン済みのユーザーを使うかどうか | 使えない |
| `installation_confirmation_handler` | CLIのインストール確認を行うコールバック | 無視される |
| `entra` | Microsoft Entra認証の設定（`EntraAuthConfig` または配列） | 共通 |
| `on_github_telemetry` | GitHubテレメトリ通知を受け取るコールバック | 共通 |
| `extension_launch_provider` | 拡張機能の起動解決を行うコールバック | 共通 |
| `network` | サンドボックスのネットワークプロキシ設定（`scope` / `resolveProxy` / `authenticateNegotiate`） | 共通 |

`github_token` と `use_logged_in_user` は、外部サーバーが自身の認証を管理するTCPモードでは使えません。設定したままTCP接続すると例外になります。トークンの実行時切り替えについては [GitHubトークン](github-token.md) も参照してください。

多くのオプションには snake_case と camelCase の両方の名前が使えます（例: `session_fs` と `sessionFs`、`builtin_plugin_directories` と `builtinPluginDirectories`、`request_handler` と `requestHandler`）。
