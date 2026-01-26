# Session `send()` と `on()` の使い方

`sendAndWait()`はレスポンスがすぐに返って来るので分かりやすいけど **最後のアシスタントメッセージ** しか受け取れない。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(prompt: 'Tell me something about Laravel.');
    dump($response->content());
});
```

途中のメッセージも受け取りたい場合は`on()`でイベントリスナーを登録する。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session) {
    $session->on(function (SessionEvent $event): void {
        if ($event->isAssistantMessage()) {
            dump($event->content());
        } else {
            dump($event);
        }
    });

    $message_id = $session->send(prompt: 'Tell me something about Laravel.');

    // whileループでメッセージの受信を待つ
    $session->wait(timeout: 60.0);
});
```

PHPでこれは分かりにくいので`on()`と`sendAndWait()`の組み合わせがおすすめ。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session) {
    $session->on(function (SessionEvent $event): void {
        if ($event->isAssistantMessage()) {
            dump($event->content());
        } else {
            dump($event);
        }
    });

    $response = $session->sendAndWait(prompt: 'Tell me something about Laravel.');

    // whileループで待つ部分はsendAndWait()内で処理されているのでこの時点で最後のメッセージまで届いている。
    // 途中のメッセージは上のon()で受け取っている。

    // sendAndWaitからの最後のメッセージは不要。
    // dump($response->content());
});
```
