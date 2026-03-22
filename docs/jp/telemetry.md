# OpenTelemetry

Copilot CLIにはOpenTelemetryによるトレース機能が組み込まれています。SDK側で設定するだけで、CLIプロセスのトレースデータを収集できます。

## 基本的な使い方

### CLIプロセスのトレース有効化

`config/copilot.php`にtelemetry設定を追加するか、直接オプションで指定します。

```php
// config/copilot.php
'telemetry' => [
    'otlpEndpoint' => 'http://localhost:4318',
],
```

または直接指定:

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\TelemetryConfig;

Copilot::useStdio([
    'telemetry' => new TelemetryConfig(
        otlpEndpoint: 'http://localhost:4318',
    )
]);

$response = Copilot::run(prompt: 'Hello');
```

クライアントを直接使う場合:

```php
use Revolution\Copilot\Client;
use Revolution\Copilot\Types\TelemetryConfig;

$client = new Client([
    'telemetry' => new TelemetryConfig(
        otlpEndpoint: 'http://localhost:4318',
    ),
]);
```

### TelemetryConfigオプション

| オプション | 説明 |
|---|---|
| `otlpEndpoint` | OTLP HTTPエンドポイントURL |
| `filePath` | JSON-linesトレース出力のファイルパス |
| `exporterType` | `"otlp-http"` または `"file"` |
| `sourceName` | インストルメンテーションスコープ名 |
| `captureContent` | メッセージ内容（プロンプト、レスポンス）をキャプチャするか |

## W3C Trace Context プロパゲーション

> **ほとんどのユーザーにはこの機能は不要です。** 上記のTelemetryConfigだけでCLIのトレースを収集できます。以下は**アプリケーション側で独自のOpenTelemetryスパンを作成し、CLIのスパンと同じ分散トレースに表示させたい場合**の高度な機能です。

SDKは`session.create`、`session.resume`、`session.send`のJSON-RPCリクエストにW3C Trace Context（`traceparent`/`tracestate`）を自動的に注入します。

### 自動プロパゲーション（推奨）

`open-telemetry/api`パッケージをインストールするだけで、トレースコンテキストが自動的に伝搬されます。

```shell
composer require open-telemetry/api open-telemetry/sdk
```

インストール後は特別な設定不要で、アプリケーションのスパンとCLIのスパンが同一の分散トレースにリンクされます。

### カスタムプロバイダー

独自のトレースコンテキスト取得ロジックを使いたい場合:

```php
use Revolution\Copilot\Support\TraceContext;

TraceContext::useProvider(function (): array {
    return [
        'traceparent' => '00-traceid-spanid-01',
        'tracestate' => 'vendor=value',
    ];
});
```

### SDK → CLI（アウトバウンド）

以下のRPC呼び出しに自動的に`traceparent`/`tracestate`が注入されます:

- `session.create` — セッション作成時
- `session.resume` — セッション再開時
- `session.send` — メッセージ送信時

### CLI → SDK（インバウンド）

CLIがツールを呼び出す際、ツールハンドラの`$invocation`配列にトレースコンテキストが含まれます:

```php
$session->registerTools([
    [
        'name' => 'my-tool',
        'description' => 'My custom tool',
        'handler' => function (array $args, array $invocation) {
            // CLIのスパンからのトレースコンテキスト
            $traceparent = $invocation['traceparent'] ?? null;
            $tracestate = $invocation['tracestate'] ?? null;

            // open-telemetry/apiがインストールされていれば
            // コンテキストは自動的に復元され、
            // ここで作成するスパンはCLIのスパンの子になります

            return 'result';
        },
    ],
]);
```

`open-telemetry/api`がインストールされている場合、ツールハンドラ実行中のOpenTelemetryコンテキストはCLIのスパンに自動的にリンクされます。明示的に`traceparent`を使う必要はありません。

## 依存関係

| パッケージ | 用途 |
|---|---|
| `open-telemetry/api` | 自動Trace Context伝搬（suggest、オプション） |
| `open-telemetry/sdk` | トレースデータのエクスポート |

`open-telemetry/api`はcomposer.jsonの`suggest`に含まれています。インストールされていなくても、SDK自体は正常に動作します。
