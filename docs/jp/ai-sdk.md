# Laravel AI SDK Integration

- 実験的な実装
- テキスト生成のみ対応。他の機能は対応していません。

Laravel AI SDKをインストールしている時のみ有効化されるオプトイン機能です。

```shell
composer require laravel/ai
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
```

`config/ai.php`に設定を追加

```php
// config/ai.php
    'default' => 'copilot',

    'providers' => [
        'copilot' => [
            'driver' => 'copilot',
            'key' => '',
        ],
    ],
```

`agent()`ヘルパーでの使い方。

```php
use function Laravel\Ai\agent;

$response = agent(
    instructions: 'You are an expert at software development.',
)->prompt('Tell me about Laravel');

echo $response->text;
```

ストリーミングも対応。`TextDelta`以外は未実装です。

```php
use Laravel\Ai\Streaming\Events\TextDelta;

use function Laravel\Ai\agent;

$stream = agent(
    instructions: 'You are an expert at software development.',
)->stream('Tell me about Laravel');

foreach ($stream as $event) {
    if ($event instanceof TextDelta) {
        echo $event->delta;
    }
}
```

Agentクラスを作る通常のLaravel AI SDKの使い方も可能ですが一部の機能にしか対応していません。
