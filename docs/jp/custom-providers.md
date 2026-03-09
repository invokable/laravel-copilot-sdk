# Custom Providers

カスタムプロバイダー（BYOK - Bring Your Own Key）を使用すると、独自のAPIキーでOpenAI互換のAPIエンドポイントに接続できます。Ollamaなどのローカルプロバイダーもサポートしています。

## ProviderConfig

`ProviderConfig`クラスは以下のプロパティを持ちます。

| プロパティ         | 型              | 説明                                                           |
|---------------|----------------|--------------------------------------------------------------|
| `baseUrl`     | `string`       | **必須**。APIエンドポイントのURL                                        |
| `type`        | `string\|null` | プロバイダータイプ。`openai`（デフォルト）、`azure`、`anthropic`                |
| `wireApi`     | `string\|null` | APIフォーマット（openai/azureのみ）。`completions`（デフォルト）または`responses` |
| `apiKey`      | `string\|null` | APIキー。Ollamaなどのローカルプロバイダーでは不要                                |
| `bearerToken` | `string\|null` | Bearer Token認証用。`apiKey`より優先される                              |
| `azure`       | `array\|null`  | Azure固有のオプション。`['apiVersion' => '2024-10-21']`など             |

## 基本的な使い方

カスタムプロバイダーを使用する場合、`model`パラメータは**必須**です。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\SessionConfig;

$response = Copilot::run(
    prompt: 'Hello!',
    config: new SessionConfig(
        model: 'gpt-4', // カスタムプロバイダー使用時は必須
        provider: new ProviderConfig(
            baseUrl: 'https://my-api.example.com/v1',
            apiKey: config('services.openai.key'),
        ),
    ),
);
```

配列形式でも指定可能です。

```php
$response = Copilot::run(
    prompt: 'Hello!',
    config: [
        'model' => 'gpt-4',
        'provider' => [
            'baseUrl' => 'https://my-api.example.com/v1',
            'apiKey' => config('services.openai.key'),
        ],
    ],
);
```

## Ollama（ローカルプロバイダー）

Ollamaなどのローカルプロバイダーでは`apiKey`は不要です。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\SessionConfig;

$response = Copilot::run(
    prompt: 'Hello!',
    config: new SessionConfig(
        model: 'deepseek-coder-v2:16b',
        provider: new ProviderConfig(
            type: 'openai',
            baseUrl: 'http://localhost:11434/v1',
        ),
    ),
);
```

## Azure OpenAI

Azure OpenAIを使用する場合、以下の点に注意してください。

- `type`は必ず`azure`を指定（`openai`ではなく）
- `baseUrl`にはホストのみを指定（`/openai/v1`などのパスは含めない）

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\SessionConfig;

$response = Copilot::run(
    prompt: 'Hello!',
    config: new SessionConfig(
        model: 'gpt-4',
        provider: new ProviderConfig(
            type: 'azure', // Azureエンドポイントでは必ず'azure'を指定
            baseUrl: 'https://my-resource.openai.azure.com', // ホストのみ
            apiKey: config('services.azure.openai_key'),
            azure: [
                'apiVersion' => '2024-10-21',
            ],
        ),
    ),
);
```

## 重要な注意点

- カスタムプロバイダー使用時は`model`パラメータが**必須**です。指定しないとエラーになります。
- Azureエンドポイント（`*.openai.azure.com`）では、必ず`type: 'azure'`を使用してください。
- `baseUrl`はホストのみを指定し、パスの構築はSDKが自動的に行います。

## onListModels ハンドラー

BYOKモードでカスタムプロバイダーを使用している場合、`client.listModels()`がCLIサーバーではなくカスタムハンドラーを呼び出すように設定できます。

`on_list_models`オプションをクライアント設定に渡します。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\ModelInfo;

// AppServiceProvider などで設定
Copilot::forceClient(fn () => app(\Revolution\Copilot\Client::class, [
    'options' => [
        'on_list_models' => function (): array {
            // カスタムプロバイダーで利用可能なモデルを返す
            return [
                ['id' => 'my-model-1', 'name' => 'My Model 1', 'version' => '1.0'],
                ['id' => 'my-model-2', 'name' => 'My Model 2', 'version' => '2.0'],
            ];
        },
    ],
]));

// on_list_models が設定されている場合、接続なしでも listModels() が呼び出せる
$models = Copilot::client()->listModels();
```

`on_list_models`が設定されている場合、`listModels()`はCLIサーバーへの接続を必要とせず、キャッシュも使用しません。
