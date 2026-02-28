# Tools

Copilot CLIのビルトインツールはデフォルトで有効です。ここで指定できるのはカスタムツールです。

## 基本的な使い方

SessionConfigの `tools` にツールの定義を指定します。

`Tool::define()`は他言語版の`defineTool`と同様のヘルパーです。  
parametersにはLaravel自身がLaravel MCPで使っているJsonSchemaを使用可能です。JsonSchemaを使わず直接配列を指定も可能です。

```php
use Illuminate\Support\Facades\Artisan;
use Illuminate\JsonSchema\JsonSchema;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\Tool;
use Revolution\Copilot\Types\ToolResultObject;

use function Laravel\Prompts\{info, note, spin, warning};

Artisan::command('copilot:tools', function () {
    $facts = [
        'PHP' => 'A popular general-purpose scripting language that is especially suited to web development.',
        'Laravel' => 'A web application framework with expressive, elegant syntax.',
    ];

    $parameters = JsonSchema::object(
        properties: [
            'topic' => JsonSchema::string()
                ->description('Topic to look up (e.g., "PHP", "Laravel")')
                ->required(),
        ],
    )->toArray();

    $config = new SessionConfig(
        tools: [
            Tool::define(
                name: 'lookup_fact',
                description: 'Returns a fun fact about a given topic.',
                parameters: $parameters,
                handler: function (array $params) use ($facts): array {
                    $topic = $params['topic'] ?? '';

                    $fact = $facts[$topic] ?? null;

                    if (! $fact) {
                        return new ToolResultObject(
                            textResultForLlm: "No fact stored for {$topic}.",
                            resultType: 'failure',
                            sessionLog: "lookup_fact: missing topic {$topic}",
                            toolTelemetry: [],
                        );
                    }

                    return new ToolResultObject(
                        textResultForLlm: $fact,
                        resultType: 'success',
                        sessionLog: "lookup_fact: served {$topic}",
                        toolTelemetry: [],
                    );
                },
            ),
        ],
    );

    Copilot::start(function (CopilotSession $session) {
        info('Starting Copilot with tools: '.$session->id());

        $prompt = 'You can call the lookup_fact tool. Use lookup_fact to tell me something about Laravel.';

        warning($prompt);

        $response = spin(
            callback: fn () => $session->sendAndWait($prompt),
            message: 'Copilot thinking...',
        );

        note($response->content());
    }, config: $config);
});
```
