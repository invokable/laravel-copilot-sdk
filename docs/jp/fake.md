# Testing

Laravelならこういうテストコードを書きたい。

## Copilot::fake()

`Copilot::fake()`はCopilot Facadeから使う機能のためのモック。他の機能はモックしない。

```php
use Revolution\Copilot\Facades\Copilot;

Copilot::fake('2'); // 常に'2'を返すモック

$response = Copilot::run(prompt: '1 + 1'); // ここで実際にはCopilot CLIは呼ばれない

// Pest
expect($response->content())->toBe('2');
// PHPUnit
$this->assertEquals('2', $response->content());
```

Copilot::startで複数呼び出した場合。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Contracts\CopilotSession;

Copilot::fake([
    '*' => Copilot::sequence()
            ->push(Copilot::response('2'))
            ->push(Copilot::response('4')),
]);

Copilot::start(function (CopilotSession $session) use (&$response1, &$response2) {
    $response1 = $session->sendAndWait(prompt: '1 + 1'); // '2'を返す
    $response2 = $session->sendAndWait(prompt: '2 + 2'); // '4'を返す
});

expect($response1->content())->toBe('2');
```

## アサーション

特定のプロンプトが呼び出されたことを確認。

```php
Copilot::assertPrompt('1 + *');
```

プロンプトが呼び出されなかったことを確認。
```php
Copilot::assertNotPrompt('1 + *');
```

Assert the number of prompts called.

```php
Copilot::assertPromptCount(3);
```

Assert that no prompts were called.

```php
Copilot::assertNothingSent();
```

## Prevent stray requests

JSON-RPCリクエストを全て防止。呼び出した場合は例外`Revolution\Copilot\Exceptions\StrayRequestException`が発生。

```php
Copilot::preventStrayRequests();
```
一部のコマンドだけは許可する場合。
```php
Copilot::preventStrayRequests(allow: ['ping']);
```
防止の停止。
```php
Copilot::preventStrayRequests(false);
```
防止するのはJSON-RPCリクエストだけなのでClientのstartは防止しない。

## 正常に動かないかもしれない使い方

Artisanコマンド内でCopilotを使っている場合、fake()でのモックは有効だけどその後のassertPrompt()などが正しく動かない場合がある。使用例が少ないので調査中。

```php
use Revolution\Copilot\Facades\Copilot;

Copilot::fake('Hello');

$this->artisan('copilot:hi');

Copilot::assertPrompt('Hi');
```

## shouldReceive() / expects()

Mockeryでお馴染みの`shouldReceive()`や`expects()`も当然使える。  
Facadeを使えば自動的に対応してる機能なので説明は省略。
