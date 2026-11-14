# キュー・スケジュール・タスク

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
// queue: キューに積まれたユーザー向け項目の確認・管理
$pending = $session->rpc()->queue()->pendingItems();
// $pending->items - QueuePendingItems の配列
// $pending->items[0]->kind - QueuePendingItemsKind（Command / Message）

$removed = $session->rpc()->queue()->removeMostRecent();
// $removed->removed - 直近項目を削除できたかどうか

$session->rpc()->queue()->clear();

// 指定位置に項目を挿入（position は 0 始まり）
use Revolution\Copilot\Types\Rpc\QueueInsertAtRequest;
use Revolution\Copilot\Types\Rpc\QueueInsertMessage;

$queue = $session->rpc()->queue();
$inserted = $queue->insertAt(new QueueInsertAtRequest(
    position: 0,
    message: new QueueInsertMessage(prompt: 'Review the changes'),
));
// $inserted->id - 挿入された項目の ID

$queue->moveItem(['id' => $inserted->id, 'toPosition' => 1]);
$duplicated = $queue->duplicateAt(['id' => $inserted->id]);
$queue->updateText(['id' => $duplicated->id, 'prompt' => 'Review and test the changes']);
$removedAt = $queue->removeAt(['id' => $duplicated->id]);
// $removedAt->removed - 項目を削除できたかどうか

$sent = $queue->sendNow(['id' => $inserted->id]);
// $sent->steered - 実行中のターンに送られたかどうか

// ドレインを一時停止する。停止は自動解除されないため、必ず false で再開する
$queue->setDrainPaused(['paused' => true]);
$queue->setDrainPaused(['paused' => false]);

// schedule (experimental: スケジュール済みプロンプト管理)
$schedule = $session->rpc()->schedule()->list();
// $schedule->entries - ScheduleEntry の配列

use Revolution\Copilot\Types\Rpc\ScheduleStopRequest;

$stopped = $session->rpc()->schedule()->stop(new ScheduleStopRequest(id: 1));
// $stopped->stopped - 停止に成功したかどうか

// tasks (experimental: バックグラウンドタスク管理)
// エージェントタスクを開始してIDを取得
use Revolution\Copilot\Types\Rpc\TasksStartAgentRequest;

$result = $session->rpc()->tasks()->startAgent(new TasksStartAgentRequest(
    agentType: 'explore',
    prompt: 'Search for all TODO comments in the codebase',
    name: 'todo-search',
    description: 'Find TODO items', // 任意
    model: 'gpt-4o', // 任意
));
// $result->agentId - バックグラウンドタスクのID

// 現在のタスク一覧を取得
$list = $session->rpc()->tasks()->list();
// $list->tasks - TaskAgentInfo、TaskShellInfo、TaskClientInfo の配列

// 切り離されたシェルの出力をカーソルで増分読み取り
use Revolution\Copilot\Types\Rpc\TasksReadOutputRequest;

$cursor = null;
do {
    $output = $session->rpc()->tasks()->readOutput(new TasksReadOutputRequest(
        id: 'shell-1',
        cursor: $cursor,
        waitMs: 1000,
    ));
    echo $output->text;
    $cursor = $output->cursor;
} while ($output->exit === null);
// $output->exit->status - タスクの終了状態
// $output->exit->exitCode - プロセスの終了コード（nullable）

// 1 回の読み取りは最大 64 KiB。waitMs は既定値 0、最大 30000ms。
// stdout と stderr は統合される。カーソルが無効になった場合はカーソルなしで再読み取りする。

// アプリケーション所有のジョブをタスクとして登録
use Revolution\Copilot\Types\Rpc\TasksRegisterRequest;

$registered = $session->rpc()->tasks()->register(new TasksRegisterRequest(
    clientTaskId: 'import-123',
    description: '顧客データをインポート',
    cancellable: true,
    displayName: '顧客インポート', // 任意
));
// $registered->task->id - CLI が割り当てたタスクID
// $registered->created / $registered->reclaimed - 新規作成・孤立タスクの再取得状態

// クライアント所有タスクの進捗を更新
use Revolution\Copilot\Enums\TaskClientActiveStatus;
use Revolution\Copilot\Enums\TaskClientUpdateKind;
use Revolution\Copilot\Types\Rpc\TaskClientUpdate;
use Revolution\Copilot\Types\Rpc\TasksUpdateRequest;

$updated = $session->rpc()->tasks()->update(new TasksUpdateRequest(
    id: $registered->task->id,
    sequence: 1,
    update: new TaskClientUpdate(
        kind: TaskClientUpdateKind::Progress,
        status: TaskClientActiveStatus::Running,
        phase: 'importing',
        percentage: 25,
    ),
));
// sequence は更新ごとに増加させる

// 完了を報告
$completed = $session->rpc()->tasks()->update(new TasksUpdateRequest(
    id: $registered->task->id,
    sequence: 2,
    update: new TaskClientUpdate(
        kind: TaskClientUpdateKind::Completed,
        result: ['imported' => 100],
    ),
));

// タスクの進捗を取得（未追跡の場合は null）
$progress = $session->rpc()->tasks()->getProgress(['id' => $registered->task->id])->progress;
// TaskAgentProgress、TaskClientProgress、TaskShellProgress、または null

// 切り離されたシェルの状態を更新し、バックグラウンド作業の完了を待つ
$session->rpc()->tasks()->refresh();
$session->rpc()->tasks()->waitForPending();

// 現在バックグラウンド化できるタスクを取得、またはそのタスクを即時にバックグラウンド化
$promotable = $session->rpc()->tasks()->getCurrentPromotable()->task;
$promotedCurrent = $session->rpc()->tasks()->promoteCurrentToBackground()->task;

// タスクをバックグラウンドモードに移行（同期待ちを解除）
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundRequest;

$promoted = $session->rpc()->tasks()->promoteToBackground(
    new TasksPromoteToBackgroundRequest(id: $result->agentId)
);
// $promoted->promoted - 移行に成功したかどうか

// タスクをキャンセル
use Revolution\Copilot\Types\Rpc\TasksCancelRequest;

$cancelled = $session->rpc()->tasks()->cancel(
    new TasksCancelRequest(id: $result->agentId)
);
// $cancelled->cancelled - キャンセルに成功したかどうか

// 完了またはキャンセル済みのタスクを削除
use Revolution\Copilot\Types\Rpc\TasksRemoveRequest;

$removed = $session->rpc()->tasks()->remove(
    new TasksRemoveRequest(id: $result->agentId)
);
// $removed->removed - 削除に成功したかどうか

// 実行中のエージェントタスクにメッセージを送信（ステアリング）
use Revolution\Copilot\Types\Rpc\TasksSendMessageRequest;

$sent = $session->rpc()->tasks()->sendMessage(
    new TasksSendMessageRequest(id: $result->agentId, message: '別のディレクトリも検索してください')
);
// $sent->sent - メッセージが正常に送信されたかどうか
// $sent->error - 送信失敗時のエラーメッセージ（nullable）

// メインターンだけを中断し、キュー内のプロンプトは次のターン用に保持
use Revolution\Copilot\Types\Rpc\InterruptMainTurnRequest;

$interrupted = $session->rpc()->interruptMainTurn(
    new InterruptMainTurnRequest(flushQueued: true)
);
// $interrupted->interrupted - 実行中のメインターンを中断できたかどうか

// メインターンを中断せず、実行中のバックグラウンドエージェントをすべてキャンセル
$cancelledCount = $session->rpc()->cancelAllBackgroundAgents();
// キャンセルされたエージェント数
```
