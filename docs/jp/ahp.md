# AHP ホスト

Agent Host Protocol（AHP）を使うと、Copilot ランタイムが管理するリスナーを起動し、WebSocket または GitHub Mission Control 経由でリモートクライアントを受け付けられます。AHP ホストは `Client` 接続に所有されます。`Client::stop()` またはホストの `dispose()` でリスナーを停止しても、ホストが利用したセッション自体は削除されません。

## ホストの起動

少なくとも `localServer` または `githubEnvironment` を指定します。認証方式も必須です。匿名アクセスを許可する場合も `none` を明示してください。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\AhpHostAuthentication;

$client = Copilot::client();

$host = $client->startAhpHost(
    authentication: AhpHostAuthentication::github(),
    localServer: [],
    allowCreatingNewSessions: true,
    workspace: '/workspace/project',
);

echo $host->url;
```

`localServer: []` はループバックアドレスと空いているポートを使います。`githubEnvironment` を指定する場合は `github` 認証が必要です。ホストが返す `token` は接続用の秘密情報として扱ってください。

`workspace` は AHP ホストで使う作業ディレクトリです。パスは Copilot ランタイムが動作するホスト上で解釈されるため、Laravel アプリケーション側のパスと同じとは限りません。省略時は workspace を RPC に含めず、ランタイム側の既存動作を維持します。

## 認証モード

- `AhpHostAuthentication::github()` は GitHub アカウントによる認証を使います。`githubEnvironment` と組み合わせられます。
- `AhpHostAuthentication::none()` は認証なしを明示します。
- `AhpHostAuthentication::custom()` はアプリケーション独自の入場トークン検証を行います。安定した所有者 identity と検証 callback が必要です。

custom 認証 callback には、復号済み入場トークンを含む `HostValidateAuthenticationRequest` と、キャンセル通知を受け取る `CancellationToken` が渡されます。成功時は identity と将来の RFC3339 形式の `expiresAt` を返し、拒否時は `null` を返します。identity は `requireIdentity` と完全一致する必要があります。

```php
use Revolution\Copilot\Types\AhpHostAuthentication;
use Revolution\Copilot\Types\Rpc\HostAuthenticationResult;
use Revolution\Copilot\Types\Rpc\HostValidateAuthenticationRequest;
use Revolution\Copilot\Support\CancellationToken;

$authentication = AhpHostAuthentication::custom(
    requireIdentity: 'team-123',
    authenticate: function (
        HostValidateAuthenticationRequest $request,
        CancellationToken $signal,
    ): ?HostAuthenticationResult {
        if ($signal->isCancellationRequested()) {
            return null;
        }

        $identity = validateAdmissionToken($request->token);

        if ($identity !== 'team-123') {
            return null;
        }

        return new HostAuthenticationResult(
            identity: $identity,
            expiresAt: '2099-01-01T00:00:00Z',
        );
    },
);

$host = $client->startAhpHost(
    authentication: $authentication,
    localServer: [],
);
```

入場トークンは推論用の GitHub token や provider token とは別の資格情報です。ログやセッション設定に保存しないでください。`host.authenticationCancelled` やホストの終了を受け取ると callback の signal がキャンセルされます。外部処理を行う場合は `onCancellationRequested()` で中断処理を登録してください。

## 新規セッション受付ポリシー

`allowCreatingNewSessions` はリモートクライアントによる新規セッション作成の初期ポリシーです。省略時は拒否されます。所有アプリケーションが作成するセッション、公開済みセッション、既存接続には適用されません。

稼働中にポリシーを切り替える場合は、ホストの `setSessionCreationPolicy()` を呼び出します。変更の確認後に到着した新規リクエストから適用され、すでに受け付けた作成処理は完了できます。接続済みのクライアントは切断されません。

```php
$host->setSessionCreationPolicy(allowCreatingNewSessions: false);
```

RPC を直接使う場合は `$client->rpc()->host()->setSessionCreationPolicy()` を呼び出し、`HostSetSessionCreationPolicyRequest` に `hostId` と新しい設定を渡します。

## アプリケーション所有セッション

`createSession` と `resumeSession` callback を指定すると、AHP ホストからの要求をアプリケーションの `Client` に渡せます。callback には、ランタイムが検証した `HostSessionAuthentication`、ホスト指定の `config`、協調キャンセル用の `CancellationToken` を含む `HostMaterializeSessionRequest` が渡されます。返すセッションは同じ `Client` で作成または再開したものにしてください。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\Rpc\HostMaterializeSessionRequest;
use Revolution\Copilot\Support\PermissionHandler;

$host = $client->startAhpHost(
    authentication: $authentication,
    localServer: [],
    createSession: function (
        HostMaterializeSessionRequest $request,
        CancellationToken $signal,
    ) use ($client): CopilotSession {
        return $client->createSession([
            ...($request->config ?? []),
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);
    },
    resumeSession: function (
        HostMaterializeSessionRequest $request,
        CancellationToken $signal,
    ) use ($client): CopilotSession {
        $sessionId = $request->config['sessionId'];

        return $client->resumeSession($sessionId, [
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);
    },
    onSessionReleased: function (CopilotSession $session): void {
        // AHP の参加が終了したときのアプリケーション側の後処理
    },
);
```

`onSessionReleased` は参加が終了したセッションを受け取り、SDK はセッションを切断・破棄しません。`HostMaterializeSessionRequest::$authentication` はクライアント入力ではなく、ホストが確認した trusted identity です。callback 側でも必要な tenant やユーザーの認可を行ってください。

## セッションカタログと終了

```php
$host->publishSession('session-id');
$sessions = $host->listSessions();
$host->unpublishSession('session-id');
$host->dispose();
```

公開解除は AHP カタログからの取り下げであり、ローカル履歴や実行中の処理を削除・停止しません。`dispose()` はリスナーを停止しますが、関連セッションの削除は行いません。

## 関連ドキュメント

- [RPC](./rpc.md)
- [リモートセッション](./remote-sessions.md)
