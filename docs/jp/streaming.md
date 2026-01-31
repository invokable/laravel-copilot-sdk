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
