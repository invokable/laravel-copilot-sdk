# リモート・サンドボックス

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## remote (experimental: リモートセッションサポート)

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

## sandbox (experimental: サンドボックス制御)

```php
// セッション作成前にホストのサンドボックス対応状況を確認
$hostSupport = $client->rpc()->sandbox()->getHostSupport();
// $hostSupport->supported, $hostSupport->reason, $hostSupport->capabilities

// Proxy CAの要否とインストール対応状況を確認
$proxyCa = $client->rpc()->sandboxProxyCa();
$relevant = $proxyCa->checkRelevance(['enabled' => true]);
$installable = $proxyCa->checkInstallationSupport();

use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionRequest;
use Revolution\Copilot\Types\Rpc\SandboxGrantPathForRequestRequest;
use Revolution\Copilot\Types\Rpc\SessionSandboxGetRemoteMcpEgressRequest;

// サンドボックス強制状態を取得
$status = $session->rpc()->sandbox()->getEnforcementStatus();

// 新しいサンドボックス admission に対して強制適用可能か確認
$session->rpc()->sandbox()->ensureEnforcementAvailable();

// リモートMCPエンドポイントのegressを判定
$egress = $session->rpc()->sandbox()->getRemoteMcpEgress(
    new SessionSandboxGetRemoteMcpEgressRequest(
        secretConfigDir: '/path/to/mcp-secrets',
        url: 'https://mcp.example.com',
    ),
);
// deniedReason は拒否時のみ、proxyUrl は許可されたプロキシ経由の場合のみ設定される

// サンドボックスの新規shellで利用できる可能性がある認証情報（値は返されない）
$credentials = $session->rpc()->sandbox()->getCredentialSuggestions();
// $credentials->suggestions は SandboxCredentialSuggestion の配列
// sandbox無効時や候補がない場合は空配列。設定やネットワーク権限は変更されない。

// 保留中のサンドボックスバイパス許可リクエストを承認し、セッション全体でサンドボックスを無効化
$result = $session->rpc()->sandbox()->disableForSession(new SandboxDisableForSessionRequest(requestId: $requestId));
// $result->success, $result->enabled

// 保留中のサンドボックスエスカレーション許可リクエストが提示したパス許可（sandboxPathGrant）を
// 承認し、そのパスをセッションのサンドボックスポリシーに追加して、ブロックされた操作を
// サンドボックス内で再実行させる
$result = $session->rpc()->sandbox()->grantPathForRequest(new SandboxGrantPathForRequestRequest(requestId: $requestId));
// 配列入力も可: ->grantPathForRequest(['requestId' => $requestId])
// $result->success
// 対象のリクエストが保留中でなくなっていたり、sandboxPathGrantを伴わなかったり、
// 現在の管理ポリシー下でグラントが無効な場合は失敗する。
// 特定のパスのみを許可する最小権限の手段であり、セッション全体を無効化するdisableForSession()より推奨される。
// このパスは永続化されない（このセッション限りで、次回以降のセッションには引き継がれない）。
// サンドボックス設定を保存するホストは、必要であれば自身でパスを保存する。
```

権限ハンドラでは、`$request['sandboxPathGrant']`がある場合に対象の保留中リクエストの`requestId`で呼び出します。このRPC自身が許可リクエストに応答するため、成功した場合は通常の応答を送らないよう`PermissionDecision::noResult()`を返します。

```php
if (isset($request['sandboxPathGrant'])) {
    $result = $session->rpc()->sandbox()->grantPathForRequest([
        'requestId' => $requestId,
    ]);

    return $result->success
        ? PermissionDecision::noResult()
        : PermissionDecision::reject();
}
```
