# Laravel Event

[Events](../../src/Events)のイベントクラスを各所に仕込んでいるのでログやデバッグに活用。

[Hooks](./hooks.md)でも似たことができるけどHooksがない場所にも仕込める。

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

例えば少し時間のかかる処理をキューやdeferに投げる。この`Copilot::run()`の結果は直接受け取れないけど代わりにMessageSendAndWaitイベントのリスナーで受け取ることができる。

```php
dispatch(fn() => Copilot::run(''));
```

```php
use function Illuminate\Support\defer;

defer(fn() => Copilot::run(''));
```
