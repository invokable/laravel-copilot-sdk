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
// セッションを開かずローカルのイベントログの有無を確認（空のログも true）
$hasEvents = Copilot::client()->rpc()->sessions()->hasEvents(['sessionId' => 'session-id']);
// $hasEvents->exists は、ログがなくメタデータのみの場合 false
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

## sessions.findByName（experimental）

`findByName()`は設定済みのローカルセッションプロファイルから友好名に一致する候補をすべて返します。`configDir`を指定すると別のプロファイルを検索できます。検索語は空白の除去、Unicode のケースフォールディングと正規化後に照合されます（保存済みの名前はトリムされません）。空白のみの検索語は一致なしになります。

候補は再開可能な履歴の存在を保証せず、結果の順序も一定ではありません。

```php
use Revolution\Copilot\Facades\Copilot;

// sessions.findByName
$result = Copilot::client()->rpc()->sessions()->findByName([
    'name' => 'My session',
    // 'configDir' => '/path/to/profile', // 省略時はサーバーの設定を使用
]);

foreach ($result->matches as $match) {
    echo $match->sessionId;
    echo $match->name;
}
```

## sessions.import（experimental）

`sessions()->import()`はセマンティックなセッション JSONL を検証し、新しいローカルセッションとして取り込みます。`jsonl`には入力全体、`inputPath`には相対添付ファイルの解決に使う入力ファイルのパス、`defaultWorkingDirectory`にはフォールバック用の絶対パスを指定します。必要に応じて`workingDirectory`と`name`で上書きできます。

`dryRun: true`では検証のみを行い、セッションを作成しません。実際に取り込む場合は`dryRun: false`を指定します。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\SessionsImportParams;

// sessions.import
$result = Copilot::client()->rpc()->sessions()->import(new SessionsImportParams(
    jsonl: $jsonl, // セマンティックなセッション JSONL の全内容
    inputPath: '/path/to/session.jsonl',
    defaultWorkingDirectory: '/path/to/project',
    dryRun: true,
));

// $result->ok, $result->code, $result->message, $result->dryRun
// $result->externalId, $result->sessionId は任意
// $result->imported は sessions / messages / toolCalls / toolResults / attachments の件数
// $result->diagnostics は line / code / message / 任意の externalId を含む検証結果
```

引数は配列でも指定できます。

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

## cloudTasks (experimental: クラウドエージェントタスク)

`cloudTasks()`はクラウドコーディングエージェントのタスク作成・削除・取得、セッションログやリプレイの取得、共有状態の変更、タスクIDから開くセッションの解決を行います。各メソッドは対応するParams型または配列を受け取り、結果を型付きDTOで返します。作成結果の`ok`が`false`の場合、サービスによる拒否理由は`reason`と`message`、実行されたHTTPリクエストの失敗情報は`statusCode`、`errorMessage`、`errorPayloadJson`で確認できます。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\CloudTasksCreateParams;

$result = Copilot::client()->rpc()->cloudTasks()->create(
    new CloudTasksCreateParams(
        authInfo: $authInfo,
        integrationId: 'my-app',
        repositoryOwner: 'octo',
        repositoryName: 'example',
    ),
);

if (! $result->ok) {
    // $result->reason and $result->message describe a declined request.
    // $result->statusCode and $result->errorMessage describe an attempted HTTP request.
}
```

## taskAnalytics (experimental: 記録済みトラジェクトリの分類)

`taskAnalytics()->classifyTrajectory()`はセッションを作成せず、記録済みのCopilotイベントまたはATIFデータを分類します。アーカイブされたツールは実行されず、会話履歴や分類テレメトリにも追加されません。`events`は入力順のまま渡し、`classifiers`、`maxConcurrentRequests`、`judge`、`previousResult`を指定できます。`validateOnly: true`なら推論せず、ローカル検証結果だけを取得できます。

通常の分類では、イベント内容はGitHub CAPIの認証情報を使ったモデル処理に送られます。自動的な秘匿化やプロンプトインジェクション対策はないため、送信を承認したうえでアーカイブの出所を検証し、必要な機密情報を事前に除去してください。分類結果は信頼できる事実ではなく、認可やセキュリティ判断には使用しないでください。

```php
$result = Copilot::client()->rpc()->taskAnalytics()->classifyTrajectory([
    'events' => $events,
    'validateOnly' => true,
]);
```

認証URLのブラウザ起動には `runtime()->openBrowser()` を使います。

```php
use Revolution\Copilot\Types\Rpc\RuntimeOpenBrowserRequest;

Copilot::client()->rpc()->runtime()->openBrowser(new RuntimeOpenBrowserRequest(
    url: 'https://example.com/authorize',
));
```

サンドボックスのパス許可警告は `sandbox()->getPathGrantWarning()` で取得します。

```php
use Revolution\Copilot\Types\Rpc\SandboxPathGrantWarningParams;

$warning = Copilot::client()->rpc()->sandbox()->getPathGrantWarning(new SandboxPathGrantWarningParams(
    resolvedPath: '/workspace/project',
    readOnly: false,
));
```

## sessions connect・Remote Control（TCP モード）

CLIサーバーから既存のリモートセッションに接続する場合は、`sessions()->connect()`を使います。接続先のリモート`sessionId`を渡すと、SDK側の`sessionId`とリモートセッションのmetadataを含む結果が返ります。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\ConnectRemoteSessionParams;

$client = Copilot::useTcp(url: 'tcp://127.0.0.1:12345')->client();

$connected = $client->rpc()->sessions()->connect(
    new ConnectRemoteSessionParams(sessionId: 'remote-session-id'),
);

echo $connected->sessionId;
// $connected->metadata - ConnectedRemoteSessionMetadata オブジェクト
```

TCPモードでは`sessions()`からランタイム全体で1つのRemote Controlも管理できます。セッション単位の`remote()->enable()`とは異なり、操作対象セッションの開始・停止・移譲や、現在の状態の取得ができます。

```php
use Revolution\Copilot\Types\Rpc\RemoteControlConfig;
use Revolution\Copilot\Types\Rpc\SessionsStartRemoteControlRequest;

$result = $client->rpc()->sessions()->startRemoteControl(
    new SessionsStartRemoteControlRequest(
        sessionId: $sessionId,
        config: new RemoteControlConfig(
            remote: true,
            steerable: true,
            explicit: true,
            silent: false,
        ),
    ),
);

if ($result->status->frontendUrl !== null) {
    echo 'Remote URL: '.$result->status->frontendUrl.PHP_EOL;
}

$status = $client->rpc()->sessions()->getRemoteControlStatus();
// 状態は off / connecting / active / error のいずれか。active では frontendUrl や isSteerable も参照できる

$client->rpc()->sessions()->setRemoteControlSteering(['enabled' => true]);
$client->rpc()->sessions()->transferRemoteControl([
    'toSessionId' => $nextSessionId,
    'expectedFromSessionId' => $sessionId,
]);
$client->rpc()->sessions()->stopRemoteControl([
    'expectedSessionId' => $nextSessionId,
]);
```

詳細は [Remote Sessions](../remote-sessions.md) を参照してください。
