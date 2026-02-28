# Streaming

SessionConfigで `streaming: true`を指定すると、Copilotからの応答をストリーミングで受け取れます。  
元からstdioモードでもTCPモードでもストリーミングのような動作です。違いは`ASSISTANT_MESSAGE_DELTA`を受信するようになることです。

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

- 流暢な表示のためには改行を追加せずそのまま表示します。
- Laravel Promptsの`spin()`は表示が崩れるので一緒に使いません。

## 具体的な使用パターン

### Artisanコマンド

上記のように`echo`もしくは`$this->output->write()`で直接出力します。Copilot CLIを直接使った場合と同じ表示なので理解しやすいです。

### WebページでServer-Sent Events (SSE)として配信

#### `response()->eventStream()`を使う書き方

`response()->eventStream()`が追加されたのはLaravel12リリース直前くらいの時期です。このパッケージはLaravel12以上のみ対応なので使用可能です。

```php
Route::get('/copilot/sse', function () {
    return response()->eventStream(function () {
        yield from Copilot::stream(function (CopilotSession $session) {
            foreach ($session->sendAndStream('Tell me something about Laravel.') as $event) {
                if ($event->isAssistantMessageDelta()) {
                    yield $event->deltaContent();
                }
            }
        }, config: new SessionConfig(streaming: true));
    });
});

Route::get('/copilot', function () {
    return view('copilot');
});
```

#### `response()->stream()`を使う書き方

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

#### フロントエンド側のコード例

`copilot.blade.php`は簡易的な表示確認用です。本番用にはReactかVueを使っているならLaravel公式のnpmパッケージを使うのが推奨です。`@laravel/stream-react`や`@laravel/stream-vue`

```html
<html>
<body>

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

<h1>Copilot SSE Test</h1>
<div id="output"></div>
</body>
</html>
```

## WebSocketでの配信

Laravelの通知機能、ブロードキャスト機能、Reverbを組み合わせて`delta`をWebSocketで配信することも可能です。

## Livewireの`wire:stream`

Livewireでも`wire:stream`ディレクティブを使ってストリーミング表示が可能です。

```php
<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionEvent;

new class extends Component
{
    public string $prompt = 'Tell me about Laravel Livewire.';
    public string $question = '';
    public string $answer = '';
    public ?string $sessionId = null;

    function submitPrompt(): void
    {
        $this->question = $this->prompt;

        $this->prompt = '';

        $this->answer = '';

        $this->js('$wire.copilot()');
    }

    public function copilot(): void
    {
        Copilot::start(function (CopilotSession $session) {
            $this->sessionId = $session->id();

            $session->on(SessionEventType::ASSISTANT_MESSAGE_DELTA, function (SessionEvent $event): void {

                $this->stream(
                    content: $event->deltaContent(),
                    to: 'answer',
                );
                $this->answer = $event->deltaContent();
            });

            $response = $session->sendAndWait(prompt: $this->question);
            $this->answer = $response->content();
        }, config: ['streaming' => true], resume: $this->sessionId);
    }
};
?>

<div>
    <div class="border border-gray-200 rounded-lg p-4 mb-6">
        @if ($question)
            <h3 class="font-extrabold mb-3">{{ $question }}</h3>

            <div wire:stream="answer">{{ $answer }}</div>
        @endif
    </div>

    <flux:input.group>
        <flux:input wire:model="prompt" placeholder="Ask Copilot"/>
        <flux:button wire:click="submitPrompt" icon="paper-airplane" icon:variant="outline"></flux:button>
    </flux:input.group>
</div>
```
