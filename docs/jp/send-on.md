# Session `send()` と `on()` の使い方

`sendAndWait()`はレスポンスがすぐに返って来るので分かりやすいですが、**最後のアシスタントメッセージ** しか受け取れません。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(prompt: 'Tell me something about Laravel.');
    dump($response->content());
});
```

途中のメッセージも受け取りたい場合は`on()`でイベントリスナーを登録します。

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

    // ループでメッセージの受信を待つ
    $session->wait(timeout: 60.0);
});
```

PHPでこれは分かりにくいので`on()`と`sendAndWait()`の組み合わせがおすすめです。

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

    // ループで待つ部分はsendAndWait()内で処理されているのでこの時点で最後のメッセージまで届いている。
    // 途中のメッセージは上のon()で受け取っている。

    // sendAndWaitからの最後のメッセージは不要。
    // dump($response->content());
});
```

## `on()`で特定のイベントタイプを指定

SessionEventType enumか文字列でイベントタイプを指定すればそのイベントのみ購読できます。

```php
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Types\SessionEvent;

$session->on(SessionEventType::ASSISTANT_MESSAGE, function (SessionEvent $event): void {
    dump($event->content());
});

$session->on('assistant.message', function (SessionEvent $event): void {
    dump($event->content());
});
```

## すべてのイベントタイプを購読

イベントタイプを指定しなければすべて購読します。

```php
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Types\SessionEvent;

$session->on(function (SessionEvent $event): void {
});

// 名前付き引数で指定もできるので
$session->on(handler: function (SessionEvent $event): void {
});

// nullを許容する動的なタイプ指定も可能。
$type = null;
$session->on(type: $type, handler: function (SessionEvent $event): void {
});
```
