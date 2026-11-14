# MCP

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## MCP Apps (experimental)

`$session->rpc()->mcp()->apps()`からMCP Apps用のRPCを呼び出せます。`listTools()`と`callTool()`では、呼び出し元のMCPサーバー名`originServerName`が必須です。`callTool()`の結果は自由形式のMCP結果として返されます。

```php
use Revolution\Copilot\Types\Rpc\MCPAppsDiagnoseRequest;
use Revolution\Copilot\Types\Rpc\MCPAppsListToolsRequest;
use Revolution\Copilot\Types\Rpc\McpAppsCallToolRequest;
use Revolution\Copilot\Types\Rpc\McpAppsReadResourceRequest;
use Revolution\Copilot\Types\Rpc\McpAppsSetHostContextDetails;
use Revolution\Copilot\Types\Rpc\McpAppsSetHostContextRequest;

$apps = $session->rpc()->mcp()->apps();

$tools = $apps->listTools(new MCPAppsListToolsRequest(
    serverName: 'my-server',
    originServerName: 'my-server',
));
$result = $apps->callTool(new McpAppsCallToolRequest(
    serverName: 'my-server',
    toolName: 'lookup',
    originServerName: 'my-server',
    arguments: ['query' => 'hello'],
));
$resource = $apps->readResource(new McpAppsReadResourceRequest(
    serverName: 'my-server',
    uri: 'ui://my-app/index.html',
));
$context = $apps->getHostContext();
$apps->setHostContext(new McpAppsSetHostContextRequest(
    context: new McpAppsSetHostContextDetails(theme: 'dark', locale: 'ja-JP'),
));
$diagnostics = $apps->diagnose(new MCPAppsDiagnoseRequest(serverName: 'my-server'));
```

`readResource()`はHTMLなどのリソースデータを返します。SDKはブラウザー描画やiframe操作を行わないため、取得した内容の描画は呼び出し側アプリケーションの責務です。

```php
// mcp (experimental: MCPサーバーの管理)
$servers = $session->rpc()->mcp()->list();
// HTTP/SSEサーバーの設定URL: $servers->servers[0]->url
$session->rpc()->mcp()->enable(new McpEnableRequest(serverName: 'server-name'));
$session->rpc()->mcp()->disable(new McpDisableRequest(serverName: 'server-name'));
$session->rpc()->mcp()->reload();
// 進行中のMCPロードをバックグラウンドに移行(待機中のターンを解放)
$session->rpc()->mcp()->moveLoadingToBackground();

// セッション内のMCPサーバーを起動・再起動（永続設定は変更しない）
use Revolution\Copilot\Types\Rpc\McpRestartServerRequest;
use Revolution\Copilot\Types\Rpc\McpStartServerRequest;

$session->rpc()->mcp()->startServer(new McpStartServerRequest(
    serverName: 'server-name',
    config: ['type' => 'http', 'url' => 'https://mcp.example.com'],
));
$session->rpc()->mcp()->restartServer(new McpRestartServerRequest(
    serverName: 'server-name',
    expectedInstallationId: 'installation-id',
));

// 自動管理されているGitHub MCPサーバーの削除と環境変数値の解決方法
$removed = $session->rpc()->mcp()->removeGitHub();
$mode = $session->rpc()->mcp()->setEnvValueMode(['mode' => 'indirect']);
// $removed->removed、$mode->mode

// MCP samplingの実行とキャンセル
use Revolution\Copilot\Types\Rpc\McpCancelSamplingExecutionParams;
use Revolution\Copilot\Types\Rpc\McpExecuteSamplingParams;

$sampling = $session->rpc()->mcp()->executeSampling(new McpExecuteSamplingParams(
    requestId: 'sampling-1',
    serverName: 'server-name',
    mcpRequestId: 'mcp-request-1',
    request: ['messages' => [], 'maxTokens' => 256],
));
$cancelled = $session->rpc()->mcp()->cancelSamplingExecution(
    new McpCancelSamplingExecutionParams(requestId: 'sampling-1'),
);

// MCPサーバーが実行中かどうかを確認
use Revolution\Copilot\Types\Rpc\McpIsServerRunningRequest;

$result = $session->rpc()->mcp()->isServerRunning(new McpIsServerRunningRequest(serverName: 'my-server'));
// $result->running - サーバーが実行中かどうか

// MCPサーバーのツール一覧を取得
use Revolution\Copilot\Types\Rpc\McpListToolsRequest;

$result = $session->rpc()->mcp()->listTools(new McpListToolsRequest(serverName: 'my-server'));
// $result->tools - McpTools の配列（name, description）

// MCPプロンプトの一覧と取得（experimental）
use Revolution\Copilot\Types\Rpc\McpPromptsGetRequest;
use Revolution\Copilot\Types\Rpc\McpPromptsListRequest;

$prompts = $session->rpc()->mcp()->listPrompts(new McpPromptsListRequest(serverName: 'my-server'));
$rendered = $session->rpc()->mcp()->getPrompt(new McpPromptsGetRequest(
    serverName: 'my-server',
    promptName: 'summarize',
    arguments: ['document' => 'README.md'],
));
// $prompts->prompts - MCPプロンプト定義
// $rendered->messages - レンダリング済みプロンプト（モデルには送信されない）

// MCPサーバーを停止
use Revolution\Copilot\Types\Rpc\McpStopServerRequest;

$session->rpc()->mcp()->stopServer(new McpStopServerRequest(serverName: 'my-server'));

// MCPリソースの一覧を取得（experimental）
use Revolution\Copilot\Types\Rpc\McpResourcesListRequest;

$result = $session->rpc()->mcp()->resources()->list(new McpResourcesListRequest(serverName: 'my-server'));
// $result->resources - McpResource の配列（uri, name, title, mimeType など）
// $result->nextCursor - 次ページのカーソル（存在する場合）

// MCPリソースを取得（experimental）
use Revolution\Copilot\Types\Rpc\McpResourcesReadRequest;

$result = $session->rpc()->mcp()->resources()->read(new McpResourcesReadRequest(serverName: 'my-server', uri: 'ui://example'));
// $result->contents - McpResourceContent の配列

// MCPリソーステンプレートの一覧を取得（experimental）
use Revolution\Copilot\Types\Rpc\McpResourcesListTemplatesRequest;

$result = $session->rpc()->mcp()->resources()->listTemplates(new McpResourcesListTemplatesRequest(serverName: 'my-server'));
// MCP OAuthログイン（認証が必要なMCPサーバー向け）
use Revolution\Copilot\Types\Rpc\McpOauthCompleteRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginRequest;
use Revolution\Copilot\Types\Rpc\McpOauthProbeRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthCancelLoginRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthPrepareLoginRequest;

$result = $session->rpc()->mcp()->login(new McpOauthLoginRequest(serverName: 'my-server'));
// $result->authorizationUrl - OAuthフローのURL（認証が必要な場合）
// ホスト管理のOAuthリダイレクトフローでは redirectUri を指定し、callback後に complete() を呼び出す
$result = $session->rpc()->mcp()->login(new McpOauthLoginRequest(
    serverName: 'my-server',
    redirectUri: 'http://localhost:3000/oauth/callback',
));
$session->rpc()->mcp()->complete(new McpOauthCompleteRequest(
    authorizationId: $result->authorizationId,
    callbackUrl: $callbackUrl,
));

// OAuth状態を確認し、所有インストールに紐づいたログインを準備・キャンセル
$probe = $session->rpc()->mcp()->oauth()->probe(new McpOauthProbeRequest(serverName: 'my-server'));
$prepared = $session->rpc()->mcp()->oauth()->prepareLogin(new SessionMcpOauthPrepareLoginRequest(
    serverName: 'my-server',
    expectedInstallationId: 'installation-id',
));
$cancelled = $session->rpc()->mcp()->oauth()->cancelLogin(new SessionMcpOauthCancelLoginRequest(
    expectedInstallationId: 'installation-id',
    loginId: $prepared->loginId,
));
```
