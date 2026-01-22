# Tools

## 基本的な使い方

SessionConfigの `tools` にツールの定義を指定。

```php
Artisan::command('copilot:tools', function () {
    $facts = [
        'PHP' => 'A popular general-purpose scripting language that is especially suited to web development.',
        'Laravel' => 'A web application framework with expressive, elegant syntax.',
    ];

    $config = new SessionConfig(
        tools: [
            [
                'name' => 'lookup_fact',
                'description' => 'Returns a fun fact about a given topic.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'topic' => [
                            'type' => 'string',
                            'description' => 'Topic to look up (e.g., "PHP", "Laravel")',
                        ],
                    ],
                    'required' => ['topic'],
                ],
                'handler' => function (array $params) use ($facts): array {
                    $topic = $params['topic'] ?? '';

                    $fact = $facts[$topic] ?? "Sorry, I don't have a fact about {$topic}.";

                    if (! $fact) {
                        return [
                            'textResultForLlm' => "No fact stored for {$topic}.",
                            'resultType' => 'failure',
                            'sessionLog' => "lookup_fact: missing topic {$topic}",
                            'toolTelemetry' => [],
                        ];
                    }

                    return [
                        'textResultForLlm' => $fact,
                        'resultType' => 'success',
                        'sessionLog' => "lookup_fact: served {$topic}",
                        'toolTelemetry' => [],
                    ];
                },
            ],
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

        note($response->getContent());
    }, config: $config);
});
```
