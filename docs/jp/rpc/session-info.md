# セッション情報・ローカルセッション管理

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## metadata (experimental: セッション情報)

```php
use Revolution\Copilot\Types\Rpc\MetadataUpdateClientMetadataRequest;

// セッションの現在のアクティビティ状態を取得
$activity = $session->rpc()->metadata()->activity();
// $activity->abortable - 中断可能な処理があるか
// $activity->hasActiveWork - ターンやバックグラウンドタスクなどの作業が実行中か

// このローカルセッションに紐づくクライアント所有メタデータを取得
$metadata = $session->rpc()->metadata()->getClientMetadata();

// メタデータをアトミックに更新（clear → remove → set の順で適用）
$metadata = $session->rpc()->metadata()->updateClientMetadata(new MetadataUpdateClientMetadataRequest(
    set: ['my-app/key' => 'value'],
));
```

## ローカルセッション管理（experimental）

同じ設定ディレクトリで管理をオプトインしたローカルセッションを、`$session->rpc()->management()`から設定・一覧・作成・メッセージ送信・クローズできます。`setSessionCreationHandler()`でホストコールバックを登録すると、`create()`からのreverse-RPCを受け取れます。コールバックは`newSessionId`と権限・モデル制限を保って子セッションを作成し、`SessionManagementCreateResult`を返す必要があります。

以下の例では、`$sessionCreationHandler`は`SessionManagementCreateHostRequest`を受け取り、ホスト側で子セッションを作成して`SessionManagementCreateResult`を返すクロージャです。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\SessionManagementScope;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\SessionManagementCloseRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementConfigureRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementListRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementMessageRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementToolOverrides;

Copilot::start(function (CopilotSession $session) use ($sessionCreationHandler): void {
    $session->setSessionCreationHandler($sessionCreationHandler);
    $management = $session->rpc()->management();

    $policy = $management->configure(new SessionManagementConfigureRequest(
        cwd: base_path(),
        enabled: true,
        scope: SessionManagementScope::All,
        tools: new SessionManagementToolOverrides(
            create: true,
            list: true,
            message: true,
            close: true,
        ),
    ));

    $peers = $management->list(new SessionManagementListRequest());
    $peer = $peers->sessions[0] ?? null;

    $child = $management->create(new SessionManagementCreateRequest(
        prompt: 'Review the current implementation.',
    ));

    if ($peer !== null) {
        $message = $management->message(new SessionManagementMessageRequest(
            targetSessionId: $peer->sessionId,
            message: 'Please report your progress.',
        ));
    }
});
```

`message()`の`accepted`は配信の受け入れを示し、応答やタスク完了を意味しません。子セッションの作業完了を別途確認した後、作成した子だけを閉じる場合は`close()`を使います。保存済み履歴は削除されません。

```php
$management->close(new SessionManagementCloseRequest(
    targetSessionId: 'child-session-id',
));
```
