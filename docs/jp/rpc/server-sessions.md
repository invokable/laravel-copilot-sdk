# セッション・ホスト・環境

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
use Revolution\Copilot\Enums\EventsReadDirection;
use Revolution\Copilot\Types\Rpc\HooksDiscoverRequest;
use Revolution\Copilot\Types\Rpc\SessionsListRequest;
use Revolution\Copilot\Types\Rpc\SessionsPruneOldRequest;
use Revolution\Copilot\Types\Rpc\SessionsReadPersistedEventsRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenCreateRequest;
use Revolution\Copilot\Enums\SessionsOpenStatus;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionListFilter;

// 新規セッションで利用可能な組み込みスラッシュコマンド一覧（experimental）
Copilot::client()->rpc()->commands()->list();

// セッション開始前のフック探索（experimental）
Copilot::client()->rpc()->hooks()->discover(new HooksDiscoverRequest(
    projectPaths: ['/path/to/project'],
));

// ホストのサンドボックス対応状況（experimental）
$hostSupport = Copilot::client()->rpc()->sandbox()->getHostSupport();
// $hostSupport->supported, $hostSupport->reason, $hostSupport->capabilities

// sessions (experimental: 永続化済みセッションの照会・管理)

// sessions.open は kind で作成・再開・resumeLast・attach・remote/cloud 接続・handoff を選択します。
$opened = $sessions->open(new SessionsOpenCreateRequest(
    options: new SessionConfig(sessionId: 'new-session', model: 'gpt-5'),
));
if ($opened->status === SessionsOpenStatus::Created) {
    echo $opened->sessionId;
}
// 配列でも指定できます。再開時の sessionId は variant のトップレベルに指定します。
$resumed = $sessions->open(['kind' => 'resume', 'sessionId' => 'existing-session']);
// 戻り値は SessionsOpenResult。not_found の場合は sessionId がありません。
// sessionApi、onTaskCreated、onProgress、onConfirm は in-process 専用で、wire payload には含まれません。

$listed = $sessions->list(new SessionsListRequest(
    source: 'local',
    metadataLimit: 50,
    filter: new SessionListFilter(cwd: '/path/to/project'),
));
// 各 SessionMetadata の isRemote で local / remote セッションを判別
foreach ($listed->sessions as $session) {
    echo $session->sessionId;
}

$sessions->findByTaskId(['taskId' => 'task-id']);
$sessions->findByPrefix(['prefix' => 'session-id-prefix']);
$sessions->getLastForContext(['context' => ['cwd' => '/path/to/project']]);
$sizes = $sessions->getSizes(); // $sizes->sizes は session ID => byte 数
$inUse = $sessions->checkInUse(['sessionIds' => ['session-id']]);
$deleted = $sessions->bulkDelete(['sessionIds' => ['session-id']]);

// アクティブなセッションを閉じる（履歴は削除しない）
$sessions->close(['sessionId' => 'session-id']);

// 保留中イベントをディスクへ保存
$sessions->save(['sessionId' => 'session-id']);

// このプロセスが保持するセッションのin-use lockを明示的に解放
$sessions->releaseLock(['sessionId' => 'session-id']);

// reloadPluginHooksはuser/plugin hooksを再読み込みする。
// folder trustの確認前はrepo hooksを遅延させ、信頼確認後に続けてロードする。
$sessions->reloadPluginHooks([
    'sessionId' => 'session-id',
    'deferRepoHooks' => true,
]);
$loadedHooks = $sessions->loadDeferredRepoHooks(['sessionId' => 'session-id']);
// $loadedHooks->startupPrompts と $loadedHooks->hookCount

// Manager-wideの追加plugin設定を置換（空配列でクリア）
$sessions->setAdditionalPlugins(['plugins' => []]);

// local session metadata の summary / context を補完
$enriched = $sessions->enrichMetadata([
    'sessions' => [[
        'sessionId' => 'session-id',
        'startTime' => '2026-10-17T09:00:00Z',
        'modifiedTime' => '2026-10-17T09:00:00Z',
        'isRemote' => false,
    ]],
]);

// close() はshutdown・イベント保存・ロック解放を行ってactive sessionを破棄しますが、
// persisted historyは削除しません。$session->disconnect() はsession.detachでこのSDKとの
// 接続とPHP側のin-memory resourcesを解放します。永続データの削除にはbulkDelete()を使います。

// セッションを開かず、cursor を使って永続化済みイベントをページング
$cursor = null;
do {
    $page = $sessions->readPersistedEvents(new SessionsReadPersistedEventsRequest(
        sessionId: 'session-id',
        cursor: $cursor,
        max: 100,
        direction: EventsReadDirection::FORWARD,
    ));

    foreach ($page->events as $event) {
        // イベントを処理
    }

    $cursor = $page->cursor;
} while ($page->hasMore);

// dryRun は削除せず候補と見積もりを返す。実削除する場合は dryRun: false を指定
$preview = $sessions->pruneOld(new SessionsPruneOldRequest(
    olderThanDays: 30,
    dryRun: true,
));

// sessions.fork
$sessions->fork(new SessionsForkRequest(
    sessionId: 'source-session-id',
    toEventId: 'evt-boundary', // オプション: この ID より前のイベントのみ含める
));

// environments (experimental: GitHub Mission Control 環境の検索・取得・削除)
$result = Copilot::client()->rpc()->environments()->list(['kind' => 'user-local']);
// $result->environments は GitHubEnvironment の配列
$env = Copilot::client()->rpc()->environments()->get(['environmentId' => 'env-id']);
Copilot::client()->rpc()->environments()->delete(['environmentId' => 'env-id']);
```

## Runtime・Host のライフサイクル（experimental）

`host.unpublishSession()` は、所有する稼働中のホストから application publication を撤回します。公開を撤回しても、元のセッション履歴、worktree、実行中の作業は削除・停止されません。すでに公開されていない場合は `unpublished` が `false` になり、application 以外の publication はエラーになります。

```php
$result = Copilot::client()->rpc()->host()->unpublishSession([
    'hostId' => 'host-1',
    'sessionId' => 'session-1',
]);
// $result->unpublished
```

`host.dispose()` は `hostId` を指定してホストリスナーを破棄します。

```php
Copilot::client()->rpc()->host()->dispose(['hostId' => 'host-1']);
```

`host.setSessionCreationPolicy()` は、稼働中のホストでリモートの新規セッション受付を切り替えます。既存クライアントを切断せず、その後の受付だけに適用します。

```php
use Revolution\Copilot\Types\Rpc\HostSetSessionCreationPolicyRequest;

Copilot::client()->rpc()->host()->setSessionCreationPolicy(
    new HostSetSessionCreationPolicyRequest(
        hostId: 'host-1',
        allowCreatingNewSessions: false,
    ),
);
```

SDKが起動したstdioランタイムでは、`Client::stop()` がセッションを切断した後、JSON-RPC接続とプロセスを閉じる前に `runtime.shutdown` を呼び出します。セッションの切断（`session.detach`）は全セッションで合計10秒の期限を共有し、期限を過ぎた切断はエラーとして戻り値に含めたうえで停止処理を続けます。ランタイムのクリーンアップ完了も最大10秒待ち、失敗時はエラーを戻り値に含めたうえで残りの停止処理を続けます。`cli_url` など外部ランタイムへの接続では、共有ランタイムを終了しないようこのRPCを呼び出しません。

CLIが対応するリクエストを特定できず`id: null`のJSON-RPCエラーを返した場合、実行中のリクエストはすべて、引用値を除去して長さを制限した診断メッセージを含む`JsonRpcException`で失敗します。接続は維持されますが、CLIがリクエストを処理済みの可能性もあるため、安全性を確認せずに自動再試行しないでください。CLIが読み取れない127階層を超える送信リクエストは送信前に拒否し、深すぎるハンドラー応答はJSON-RPCエラーに置き換えます。
