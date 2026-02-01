# Streaming

SessionConfigで `streaming: true`を指定すると、Copilotからの応答をストリーミングで受け取れる。  
元からstdioモードでもTCPモードでもストリーミングのような動作。違いは`ASSISTANT_MESSAGE_DELTA`を受信するようになること。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionEvent;

$config = new SessionConfig(
    streaming: true,
);

Copilot::start(function (CopilotSession $session) {
    $session->on(function (SessionEvent $event): void {
        if ($event->isAssistantMessageDelta()) {
            // deltaではメッセージが小分けで届く。途中経過の表示などに使う。もしくはこれだけで完結させることも可能。流暢な表示を実現できる。
            echo $event->deltaContent();
        } elseif($event->isAssistantMessage()) {
            // 小分けにされてたdeltaの送信が終わったらASSISTANT_MESSAGEとして全文を含む一つ分のメッセージが送信される。
            // 次のメッセージがあれば再度deltaで届く。
            // 一つのプロンプトに対して複数のメッセージが届くこともあるのでstreamingの場合は複数のdeltaと全文1回が繰り返される。
        } elseif($event->isAssistantReasoningDelta()) {
            // reasoning deltaも同様に小分けで届く。
            echo $event->deltaContent();
        }
    });

    $session->sendAndWait(prompt: 'Tell me something about Laravel.');
}, config: $config);
```

- 流暢な表示のためには改行を追加せずそのまま表示する。
- Laravel Promptsの`spin()`は表示が崩れるので一緒に使わない。

## 具体的な使用パターン

### Artisanコマンド

上記のように`echo`もしくは`$this->output->write()`で直接出力する。Copilot CLIを直接使った場合と同じ表示なので理解しやすい。

### WebページでServer-Sent Events (SSE)として配信

`response()->eventStream()`での使い方は成功しなかったのでひとまず`response()->stream()`を使う方法。可能ならいずれ対応を検討。

```php
Route::get('/copilot/sse', function () {
    return response()->stream(function () {
        Copilot::start(function (CopilotSession $session) {
            $session->on(function (SessionEvent $event) {
                if ($event->isAssistantMessageDelta()) {
                    echo "event: update\n";
                    echo 'data: '.$event->deltaContent()."\n\n";
                    ob_flush();
                    flush();
                }
            });

            $session->sendAndWait('Tell me something about Laravel.');
        }, config: new SessionConfig(streaming: true));

        echo "event: update\n";
        echo "data: </stream>\n\n";
        ob_flush();
        flush();
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'X-Accel-Buffering' => 'no',
    ]);
});

Route::get('/copilot', function () {
    return view('copilot');
});
```

`copilot.blade.php`は簡易的な表示確認用。本番用にはReactかVueを使っているならLaravel公式のnpmパッケージを使うのが推奨。`@laravel/stream-react`や`@laravel/stream-vue`

```html
<html>
<script>
    const source = new EventSource('/copilot/sse');

    source.addEventListener('update', (event) => {
        if (event.data === '</stream>') {
            source.close();

            return;
        }

        console.log(event.data);
        document.getElementById("output").innerHTML += event.data;
    });
</script>

<body>
    <h1>Copilot SSE Test</h1>
    <div id="output"></div>
</body>
</html>
```

## WebSocketでの配信

Laravelの通知機能、ブロードキャスト機能、Reverbを組み合わせて`delta`をWebSocketで配信することも可能。

## Livewireの`wire:stream`

Livewireでも`wire:stream`ディレクティブを使ってストリーミング表示が可能。
