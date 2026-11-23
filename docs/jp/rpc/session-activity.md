# ログ・シェル・使用量・テレメトリ・認証

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
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
use Revolution\Copilot\Types\Rpc\ShellExecuteUserRequestedRequest;
use Revolution\Copilot\Types\Rpc\ShellCancelUserRequestedRequest;

$execution = $session->rpc()->shell()->executeUserRequested(new ShellExecuteUserRequestedRequest(
    requestId: 'req-123',
    command: 'pwd',
), timeout: 120.0); // CLI 応答の待機時間（秒）。省略時は30秒
// $execution->toolCallId / success / output / exitCode / error
// 実行中に別の処理からキャンセルする場合は、同じ requestId を指定する

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

// telemetry (experimental: セッションテレメトリー)
use Revolution\Copilot\Types\Rpc\TelemetrySetFeatureOverridesRequest;

$telemetry = $session->rpc()->telemetry();
$engagement = $telemetry->getEngagementId();
// $engagement->engagementId - 利用可能な場合のエンゲージメントID。取得できない場合はnull

$telemetry->reload();
// セッションの設定ディレクトリから OpenTelemetry 設定を再読み込みします。

$telemetry->setFeatureOverrides(new TelemetrySetFeatureOverridesRequest(
    features: ['feature-key' => 'override-value'],
));
// 指定したoverrideは以後のテレメトリーイベントに付加され、以前のoverrideを置き換えます。
// SessionConfig::$enableSessionTelemetry（内部テレメトリーの有効化）とは別の機能です。

// auth: セッションの認証状態を取得
$status = $session->rpc()->gitHubAuth()->getStatus();
// $status->isAuthenticated - 認証済みかどうか
// $status->authType - 認証タイプ（AuthInfoType enum: gh-cli, token, env など）
// $status->login - GitHubログイン名
// $status->host - GitHubホスト
// $status->copilotPlan - Copilotプラン（individual, business など）
// $status->statusMessage - 認証状態のメッセージ
```

## accounts（experimental: アカウントと対話型ログイン）

`accounts()`でアカウントの一覧と現在選択中のアカウントを取得し、ログインに再利用できる保存済みIDから対話型ログインを開始できます。`authProviders()`では認証プロバイダーとその利用可否を取得できます。これらのRPCはexperimentalです。

```php
use Revolution\Copilot\Enums\AuthLoginMode;
use Revolution\Copilot\Enums\LoginProviderKind;
use Revolution\Copilot\Types\Rpc\AuthLoginBeginRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginListRequest;

// accounts: アカウント一覧と現在選択中のアカウント
$accounts = $session->rpc()->accounts();
$accountList = $accounts->list();
$active = $accounts->getActive();
// $active->account - 選択中のアカウント。存在しない場合はnull
// $active->authInfo - 認証情報のメタデータ

// authProviders: 認証プロバイダーとその利用可否
$providers = $session->rpc()->authProviders()->list();

// accounts: ログインに再利用できる保存済みID
$identities = $accounts->loginList(new AuthLoginListRequest(provider: LoginProviderKind::ENTRA));

if (isset($identities[0])) {
    $login = $accounts->begin(new AuthLoginBeginRequest(
        kind: LoginProviderKind::ENTRA,
        mode: AuthLoginMode::Default,
        accountId: $identities[0]->accountId,
    ));
}
```

保存済みIDの`label`は表示用、`active`はそのIDが現在のセッションの有効なアカウントに対応するかを示します。`accountId`はプロバイダーが所有する不透明なIDです。内容を解析・加工せず、そのままログイン開始時に渡してください。

通常のプロバイダー動作で保存済みIDを使う場合は`mode: 'default'`を指定します。プロバイダーのアカウント選択UIを明示的に表示したい場合は`mode: 'selectAccount'`を使い、この場合は`accountId`を渡しません。ログインフローの進行とキャンセルには`advance()`と`cancel()`を使います。詳細は[認証方法](../auth.md)を参照してください。
