# GitHub Copilot CLI SDK for Laravel

[![tests](https://github.com/invokable/laravel-copilot-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/invokable/laravel-copilot-sdk/actions/workflows/tests.yml)
[![Maintainability](https://qlty.sh/badges/ef9130cf-a953-4d14-ac02-9eafb4c40a0c/maintainability.svg)](https://qlty.sh/gh/invokable/projects/laravel-copilot-sdk)
[![Code Coverage](https://qlty.sh/badges/ef9130cf-a953-4d14-ac02-9eafb4c40a0c/coverage.svg)](https://qlty.sh/gh/invokable/projects/laravel-copilot-sdk)

This package is Laravel version of [GitHub Copilot CLI SDK](https://github.com/github/copilot-sdk), which allows you to interact with GitHub Copilot CLI programmatically from your Laravel applications.

## Requirements

- PHP >= 8.4
- Laravel >= 12.x
- [Copilot CLI](https://github.com/github/copilot-cli)

## Installation

```shell
composer require revolution/laravel-copilot-sdk
```

<details>
<summary>Optional operation</summary>

### .env (Optional)

```dotenv
COPILOT_CLI_PATH=copilot
```

### Publish config (Optional)

```shell
php artisan vendor:publish --tag=copilot-config
```

### Uninstall
```shell
composer remove revolution/laravel-copilot-sdk
```

</details>

## Usage

We provide a high-level API that uses a Laravel Facade on top of a layer that replicates the official SDK.

This should be sufficient for general use.

### Run single prompt

```php
use Revolution\Copilot\Facades\Copilot;

$response = Copilot::run(prompt: 'Tell me something about Laravel.');
dump($response->content());
```

### Multiple prompts in a single session

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session) use(&$content) {
    dump('Starting Copilot session: '.$session->id());

    $response = $session->sendAndWait(prompt: 'Tell me something about PHP.');
    dump($response->content());

    $response = $session->sendAndWait(prompt: 'Tell me something about Laravel.');
    dump($response->content());

    $content = $response->content();
});

dump($content);
```

## Testing

Provide Laravel way testing support with `Copilot::fake()`.

### Copilot::fake()

`Copilot::fake()` is a mock for features used from the Copilot Facade. Other features are not mocked.

```php
use Revolution\Copilot\Facades\Copilot;

Copilot::fake('2');

$response = Copilot::run(prompt: '1 + 1');

// Pest
expect($response->content())->toBe('2');
// PHPUnit
$this->assertEquals('2', $response->content());
```

When calling multiple times with Copilot::start()

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Contracts\CopilotSession;

Copilot::fake([
    '*' => Copilot::sequence()
            ->push(Copilot::response('2'))
            ->push(Copilot::response('4')),
]);

Copilot::start(function (CopilotSession $session) use (&$response1, &$response2) {
    $response1 = $session->sendAndWait(prompt: '1 + 1');
    $response2 = $session->sendAndWait(prompt: '2 + 2');
});

expect($response1->content())->toBe('2');
```

### Assertions

Assert that a specific prompt was called.

```php
Copilot::assertPrompt('1 + *');
```

Assert that a prompt was not called.

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

### Prevent stray requests

Prevent all JSON-RPC requests. If called, an exception `Revolution\Copilot\Exceptions\StrayRequestException` is thrown.

```php
Copilot::preventStrayRequests();
```

When you want to allow only some commands.

```php
Copilot::preventStrayRequests(allow: ['ping']);
```

Stop prevention.

```php
Copilot::preventStrayRequests(false);
```

Only JSON-RPC requests are prevented, so starting a client is not prevented.

## Documentation

Most of the English documentation is included in the official SDK, so we won't provide much here. README and [Getting Started Guide](./docs/getting-started.md) are provided in English.

Only Japanese documentation is available in [docs/jp](./docs/jp). **Ask Copilot!!**

## Our other packages
- [laravel-boost-copilot-cli](https://github.com/invokable/laravel-boost-copilot-cli)
- [laravel-boost-phpstorm-copilot](https://github.com/invokable/laravel-boost-phpstorm-copilot)

## License

MIT
