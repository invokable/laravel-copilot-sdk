# ステアリングとキューイング

エージェントが処理中にメッセージを送る方法として、**ステアリング**と**キューイング**の2つのパターンがあります。

## 概要

`send()` / `sendAndWait()` / `sendAndStream()` / `Copilot::run()` のすべてに `$mode` パラメータがあります。

| モード                   | 動作                       |
|-----------------------|--------------------------|
| `"enqueue"`（デフォルト）    | 現在のターンが完了した後にキューとして処理される |
| `"immediate"`（ステアリング） | 現在処理中のターンに割り込んで即座に注入される  |

## ステアリング（`"immediate"`）

エージェントが処理中のターンに直接メッセージを注入します。エージェントはリアルタイムでメッセージを受け取り、応答を調整します。進行中の作業を中断せずに方向修正したい場合に使います。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Contracts\CopilotSession;

Copilot::start(function (CopilotSession $session) {
    // 長時間かかるタスクを開始
    $messageId = $session->send(prompt: 'Refactor the authentication module to use sessions');

    // エージェントが処理中に方向修正
    $session->send(
        prompt: 'Actually, use JWT tokens instead of sessions',
        mode: 'immediate',
    );

    // アイドル状態になるまで待機
    $response = $session->sendAndWait(prompt: 'Summarize what you did');
});
```

> **注意:** ステアリングはベストエフォートです。エージェントがすでにツール呼び出しをコミットしていた場合、そのツール呼び出し完了後に適用されますが、同一ターン内で処理されます。ターンが完了した後にステアリングメッセージが届いた場合は自動的にキューに移動されます。

## キューイング（`"enqueue"`）

メッセージをキューに追加し、現在のターンが完了してから順次処理します。各キューされたメッセージは独立したターンとして実行されます。`$mode` を省略した場合のデフォルト動作です。

```php
Copilot::start(function (CopilotSession $session) {
    // 最初のタスクを開始
    $session->send(prompt: 'Set up the project structure');

    // エージェントが処理中に後続タスクをキューへ追加
    $session->send(prompt: 'Add unit tests for the auth module', mode: 'enqueue');
    $session->send(prompt: 'Update the README with setup instructions', mode: 'enqueue');

    // キューが空になるまで待つ
});
```

## Laravelでの実用的な考え方

Laravelでは `Copilot::run()` や `sendAndWait()` を使う同期的なパターンが中心です。これらはメッセージを送ってアイドル状態になるまで待機するため、**エージェントが処理中に割り込む機会がなく、`$mode` は実質的に意味をもちません**。

```php
// Copilot::run() は内部的に sendAndWait() を呼び出すため
// mode を指定しても意味がない

$response = Copilot::run(prompt: 'Tell me something about Laravel.');

// セッション内でも sendAndWait() を順番に呼ぶだけなら不要
Copilot::start(function (CopilotSession $session) {
    $r1 = $session->sendAndWait(prompt: 'First task');
    $r2 = $session->sendAndWait(prompt: 'Second task');
});
```

ステアリング（`"immediate"`）が活きるのは、`send()` で非同期的にメッセージを送りつつ、並行して別のメッセージで進行中のターンに介入できる場合です。Laravelの同期的な処理フローでは、その機会を作るのが難しいため、**基本的には `$mode` を指定せずデフォルトのままにしておくのが適切です**。

## どちらを使うべきか

| 場面                  | パターン                  |
|---------------------|-----------------------|
| エージェントが間違った方向に進んでいる | ステアリング（`"immediate"`） |
| 次にやることを思いついた        | キューイング（`"enqueue"`）   |
| 複数タスクを順番に実行したい      | キューイング（`"enqueue"`）   |
| 通常のLaravelアプリ       | デフォルト（`$mode` 不要）     |

## ベストプラクティス

- **デフォルトはキューイング** — `$mode` を省略（または `"enqueue"` を指定）するのがほとんどのケースで適切です。予測可能な動作をします。
- **ステアリングは修正用に** — `"immediate"` はエージェントが明らかに間違ったことをしている場合に限定して使います。
- **ステアリングメッセージは簡潔に** — 現在のコンテキストで理解できる短いメッセージにします。長く複雑なステアリングメッセージはかえって混乱を招きます。
- **連続ステアリングは避ける** — 短時間に複数のステアリングを送るとターンの品質が低下する可能性があります。大幅な方向転換が必要な場合はターンを中断して最初からやり直す方が適切なこともあります。

## 参考

- [基本的な使い方](./basic-usage.md)
- [Streaming](./streaming.md)
- [send-on](./send-on.md)
