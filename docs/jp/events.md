# Laravel Event

[Events](../../src/Events)のイベントクラスを各所に仕込んでいるのでログやデバッグに活用できます。

[Hooks](./hooks.md)でも似たことができますが、Hooksがない場所にも仕込めます。

```
src/
├── Events/
│   ├── Client/
│   │   ├── ClientStarted.php
│   │   ├── ToolCall.php
│   │   └── PingPong.php
│   ├── JsonRpc/
│   │   ├── MessageReceived.php
│   │   ├── MessageSending.php
│   │   └── ResponseReceived.php
│   ├── Process/
│   │   └── ProcessStarted.php
│   └── Session/
│       ├── CreateSession.php
│       ├── MessageSend.php
│       ├── MessageSendAndWait.php
│       ├── SessionEventReceived.php
│       └── ResumeSession.php
```

例えば少し時間のかかる処理をキューやdeferに投げることができます。この`Copilot::run()`の結果は直接受け取れませんが、代わりにMessageSendAndWaitイベントのリスナーで受け取ることができます。

```php
dispatch(fn() => Copilot::run(''));
```

```php
use function Illuminate\Support\defer;

defer(fn() => Copilot::run(''));
```
