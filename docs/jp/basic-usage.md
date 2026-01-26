# Laravel流ではない公式SDKに沿った使い方

Pure PHPっぽい使い方ができるようにした上でLaravelらしい使い心地を提供している。

この範囲でLaravelの機能はほとんど使ってなくどこでも使える`illuminate/support`程度なのでLaravel以外でも使えるかもしれないけどサポート対象外。Eventなどを含めているのでそのままでは使えない。

```php
use Revolution\Copilot\Client;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\SessionEvent;

$client = new Client([
    'cli_path' => 'copilot',
    'cli_args' => [],
    'cwd' => base_path(),
    'log_level' => 'info',
    'env' => [],
]);

$client->start();

/** @var Session $session */
$session = $client->createSession([
    'model' => 'gpt-5',
]);

$done = $session->on(function (SessionEvent $event) {
    if($event->isAssistantMessage()) {
        echo $event->content();
    }
});

$session->send(prompt: 'PHPではasync-awaitがまだ綺麗に書きにくい。');
$session->wait(timeout: 60.0);// True Asyncが正式に実装されるまではwaitで強制的に待ち。

$done();

$session->destroy();
$client->stop();
```
