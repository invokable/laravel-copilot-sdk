# Laravel AI SDK Integration

- Experimental implementation.
- Support only text generation. No other features are supported.

This is an opt-in feature only enabled when the Laravel AI SDK is installed.

```shell
composer require laravel/ai
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
```

Add the following configuration to `config/ai.php`.

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

Usage with agent helper.

```php
use function Laravel\Ai\agent;

$response = agent(
    instructions: 'You are an expert at software development.',
)->prompt('Tell me about Laravel');

echo $response->text;
```

Streaming

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
