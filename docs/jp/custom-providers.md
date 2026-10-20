# Custom Providers

カスタムプロバイダー（BYOK - Bring Your Own Key）を使用すると、独自のAPIキーでOpenAI互換のAPIエンドポイントに接続できます。Ollamaなどのローカルプロバイダーもサポートしています。

## ProviderConfig

`ProviderConfig`クラスは以下のプロパティを持ちます。

| プロパティ            | 型              | 説明                                                           |
|------------------|----------------|--------------------------------------------------------------|
| `baseUrl`        | `string`       | **必須**。APIエンドポイントのURL                                        |
| `type`           | `string\|null` | プロバイダータイプ。`openai`（デフォルト）、`azure`、`anthropic`                |
| `wireApi`        | `string\|null` | APIフォーマット（openai/azureのみ）。`completions`（デフォルト）または`responses` |
| `apiKey`         | `string\|null` | APIキー。Ollamaなどのローカルプロバイダーでは不要                                |
| `apiKeyCommand`  | `string\|null` | 各プロバイダーリクエストの前にCopilotランタイムが実行し、最新のAPIキーを取得するコマンド。コマンド失敗時はモデルリクエスト前にターンが失敗します。`bearerTokenProvider`を指定した場合は実行されません |
| `bearerToken`    | `string\|null` | Bearer Token認証用。`apiKey`より優先される                              |
| `bearerTokenProvider` | `callable\|null` | 各プロバイダーリクエストでBearerトークンを取得するコールバック。シリアライズされません |
| `azure`          | `array\|null`  | Azure固有のオプション。`['apiVersion' => '2024-10-21']`など             |
| `modelId`        | `string\|null` | ランタイムがエージェント設定やトークン制限の検索に使うモデル名。未設定時は`SessionConfig::$model`にフォールバック |
| `wireModel`      | `string\|null` | プロバイダーAPIに送信するモデル名。Azureのデプロイ名やカスタムファインチューン名など。未設定時は`modelId`にフォールバック |
| `maxPromptTokens` | `int\|null`  | プロンプトの最大トークン数の上書き。この制限に近づくとランタイムが会話をコンパクト化する |
| `maxOutputTokens` | `int\|null`   | 出力の最大トークン数の上書き。この制限に達すると応答が途切れる |

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

## Azure Managed Identity

有効期限付きトークンを使う場合は、`bearerTokenProvider`で各リクエスト時にトークンを取得できます。コールバックには`providerName`と`sessionId`を含む配列が渡されます。次の例では、アプリケーション側のトークン取得サービスを呼び出します。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\SessionConfig;

$response = Copilot::run(
    prompt: 'Hello!',
    config: new SessionConfig(
        model: 'gpt-4',
        provider: new ProviderConfig(
            type: 'azure',
            baseUrl: 'https://my-resource.openai.azure.com',
            azure: ['apiVersion' => '2024-10-21'],
            bearerTokenProvider: fn (array $args): string => app(AzureManagedIdentityTokenProvider::class)->getToken(),
        ),
    ),
);
```

トークンの取得・キャッシュ・更新はコールバック側で行ってください。ランタイムは各プロバイダーリクエストでコールバックを呼び出します。

## APIキーコマンド

ローテーションされるAPIキーを使う場合は、`apiKeyCommand`で各プロバイダーリクエストの前にコマンドを実行して最新のキーを取得できます。コマンドはこのパッケージではなくCopilotランタイムが実行します。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\SessionConfig;

$response = Copilot::run(
    prompt: 'Hello!',
    config: new SessionConfig(
        model: 'gpt-4',
        provider: new ProviderConfig(
            baseUrl: 'https://my-api.example.com/v1',
            apiKeyCommand: config('services.my_api.key_command'),
        ),
    ),
);
```

- `apiKeyCommand`は`null`または空白でない文字列である必要があります。空文字列・空白のみはリクエスト送信前に`InvalidArgumentException`、配列形式で文字列以外を指定した場合も`InvalidArgumentException`になります。`ProviderConfig`コンストラクターに文字列以外を渡すと、PHPの型チェックにより`TypeError`が発生します。例外メッセージにコマンドは含まれません。
- コマンドの失敗はランタイム側でターンの失敗として扱われ、モデルへのリクエストは送信されません。
- `bearerTokenProvider`を指定するとそちらが優先され、`apiKeyCommand`は実行されません。
- コマンド文字列は資格情報と同様に機密として扱い、ログや例外メッセージに出力しないでください。このパッケージもコマンドやその出力をログに記録しません。
- `apiKeyCommand`は`ProviderConfig`のみで利用できます（`NamedProviderConfig`では未対応）。

## 重要な注意点

- カスタムプロバイダー使用時は`model`パラメータが**必須**です。指定しないとエラーになります。
- Azureエンドポイント（`*.openai.azure.com`）では、必ず`type: 'azure'`を使用してください。
- `baseUrl`はホストのみを指定し、パスの構築はSDKが自動的に行います。

## onListModels ハンドラー

BYOKモードでカスタムプロバイダーを使用している場合、`client->listModels()`がCLIサーバーではなくカスタムハンドラーを呼び出すように設定できます。

`listModelsUsing()`メソッドを使って設定します。clientは初期化されやすいため、`listModels()`の前に常に`listModelsUsing()`を設定してください。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\ModelInfo;

$models = Copilot::client()->listModelsUsing(function (): array {
    // カスタムプロバイダーで利用可能なモデルを返す
    return [
        ['id' => 'my-model-1', 'name' => 'My Model 1', 'version' => '1.0'],
        ['id' => 'my-model-2', 'name' => 'My Model 2', 'version' => '2.0'],
    ];
})->listModels();
```

`null`を渡すとハンドラーを解除してデフォルトのCLIサーバー動作に戻ります。

```php
// ハンドラーを解除してデフォルト動作（CLIサーバーに問い合わせ）に戻す
$models = Copilot::client()->listModelsUsing(null)->listModels();
```

`listModelsUsing()`が設定されている場合、`listModels()`はCLIサーバーへの接続を必要とせず、キャッシュも使用しません。
