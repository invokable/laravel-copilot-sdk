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

$telemetry->setFeatureOverrides(new TelemetrySetFeatureOverridesRequest(
    features: ['feature-key' => 'override-value'],
));
// 指定したoverrideは以後のテレメトリーイベントに付加され、以前のoverrideを置き換えます。
// SessionConfig::$enableSessionTelemetry（内部テレメトリーの有効化）とは別の機能です。

// auth: セッションの認証状態を取得
$status = $session->rpc()->auth()->getStatus();
// $status->isAuthenticated - 認証済みかどうか
// $status->authType - 認証タイプ（AuthInfoType enum: gh-cli, token, env など）
// $status->login - GitHubログイン名
// $status->host - GitHubホスト
// $status->copilotPlan - Copilotプラン（individual, business など）
// $status->statusMessage - 認証状態のメッセージ
```
