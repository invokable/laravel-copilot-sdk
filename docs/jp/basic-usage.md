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

/** @var SessionEvent $response */
$response = $session->sendAndWait(prompt: 'PHPではasync-awaitがまだ綺麗に書きにくい。');

echo $response->content();

$session->destroy();
$client->stop();
```
