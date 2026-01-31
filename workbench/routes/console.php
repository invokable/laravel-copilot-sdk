<?php

declare(strict_types=1);

use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Concurrency;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\PermissionRequestKind;
use Revolution\Copilot\Types\Hooks\PreToolUseHookOutput;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;
use Revolution\Copilot\Types\SessionHooks;
use Revolution\Copilot\Types\SessionMetadata;
use Revolution\Copilot\Types\Tool;
use Revolution\Copilot\Types\UserInputRequest;
use Revolution\Copilot\Types\UserInputResponse;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;
use function Revolution\Copilot\copilot;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// vendor/bin/testbench copilot:ping
Artisan::command('copilot:ping', function () {
    $this->info(json_encode(copilot()->client()->ping()));
});

// vendor/bin/testbench copilot:version
Artisan::command('copilot:version', function () {
    $this->info(json_encode(Copilot::client()->getStatus()->toArray()));
})->purpose('Show Copilot CLI and protocol version');

// vendor/bin/testbench copilot:chat
// vendor/bin/testbench copilot:chat --resume
Artisan::command('copilot:chat {--resume}', function () {
    $mcp = json_decode(file_get_contents(__DIR__.'/../../.github/mcp-config.json'), true)['mcpServers'] ?? [];

    $config = new SessionConfig(
        onPermissionRequest: function (array $request, array $invocation) {
            info($request['intention'] ?? 'Permission requested');
            $confirm = confirm(
                label: 'Do you accept the requested permissions?',
            );

            if ($confirm) {
                return PermissionRequestKind::approved();
            } else {
                return PermissionRequestKind::deniedInteractivelyByUser();
            }
        },
        mcpServers: $mcp,
    );

    Copilot::start(function (CopilotSession $session) use ($config) {
        info('Starting Copilot chat session: '.$session->id());

        if ($this->option('resume')) {
            $sessions = collect(Copilot::client()->listSessions())
                ->mapWithKeys(function (SessionMetadata $session) {
                    return [$session->sessionId => $session->summary ?? ''];
                })
                ->toArray();

            $session_id = select(
                label: 'What session do you want to resume?',
                options: $sessions,
            );

            $session->destroy();

            $config = ResumeSessionConfig::fromArray($config->toArray());
            $session = Copilot::client()->resumeSession($session_id, $config);

            intro("Resumed previous session: $session_id. Here are the past assistant messages");

            $messages = $session->getMessages();
            foreach ($messages as $message) {
                if ($message->isAssistantMessage()) {
                    note($message->content());
                } elseif ($message->isUserMessage()) {
                    warning($message->content());
                }
            }

            outro('You can continue the conversation below.');
        }

        // sendAndWaitは最後のアシスタントメッセージのみ返す。
        // 途中のメッセージも表示したい場合はハンドラを追加。
        $session->on(function (SessionEvent $event): void {
            if ($event->isAssistantMessage()) {
                note($event->content());
            } elseif ($event->failed()) {
                error($event->errorMessage() ?? 'Unknown error');
            } else {
                info('Event: '.$event->type());
            }
        });

        while (true) {
            $prompt = text(
                label: 'Enter your prompt',
                placeholder: 'Ask me anything...',
                required: true,
                hint: 'Ctrl+C to exit',
            );

            $response = spin(
                callback: fn () => $session->sendAndWait($prompt),
                message: 'Copilot thinking...',
            );

            // 上のonハンドラで表示してるのでsendAndWaitからの最終メッセージの表示は不要。
            // 追加のハンドラを使わず最後のメッセージのみ使う場合はここで表示する。
            // note($response->content());
        }
    }, config: $config);
})->purpose('Interactive chat session with Copilot CLI SDK');

// vendor/bin/testbench copilot:tools
// https://github.com/github/copilot-sdk/blob/main/nodejs/examples/basic-example.ts を参考
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
})->purpose('Copilot Tools testing command');

// vendor/bin/testbench copilot:concurrency
Artisan::command('copilot:concurrency', function () {
    $prompt = 'Tell me something about Copilot.';

    [$gpt5_response, $sonnet_response] = Concurrency::driver('fork')->run([
        fn () => Copilot::run($prompt, config: ['model' => 'gpt-5.2'])->content(),
        fn () => Copilot::run($prompt, config: ['model' => 'claude-sonnet-4.5'])->content(),
    ]);

    info('GPT-5 Response: '.$gpt5_response);
    note('Claude Sonnet Response: '.$sonnet_response);
})->purpose('Multiple Copilot sessions with Laravel concurrency');

// vendor/bin/testbench copilot:models
Artisan::command('copilot:models', function () {
    $models = collect(Copilot::client()->listModels())
        ->map(function (ModelInfo $model) {
            return [
                'id' => $model->id,
                'name' => $model->name,
                'maxContextTokens' => $model->capabilities->maxContextWindowTokens() ?? 'N/A',
                'visionSupport' => $model->capabilities->supportsVision() ? 'Yes' : 'No',
                'supportsReasoningEffort' => $model->capabilities->supportsReasoningEffort() ? 'Yes' : 'No',
                'supportsStructuredOutputs' => $model->capabilities->supportsStructuredOutputs() ? 'Yes' : 'No',
            ];
        })->toArray();

    // config: ['model' => ''] でモデルを指定する時はIDを使う。
    table(
        headers: ['ID', 'Display Name', 'Max Context Tokens', 'Vision Support', 'Supports Reasoning Effort', 'Supports Structured Outputs'],
        rows: $models,
    );
})->purpose('List available Copilot models');

// vendor/bin/testbench copilot:mcp
Artisan::command('copilot:mcp', function () {
    $config = new SessionConfig(
        mcpServers: [
            'laravel-boost' => [
                'type' => 'local',
                'command' => './vendor/bin/testbench',
                'args' => ['boost:mcp'],
                'tools' => ['*'],
            ],
        ],
    );

    Copilot::start(function (CopilotSession $session) {
        info('Starting Copilot with Laravel Boost MCP: '.$session->id());

        $prompt = 'Copilot SDKからのMCP動作テスト。どのMCPが読み込まれている？ laravel-boostが使える場合はapplication-infoでアプリ情報を取得して。';

        warning($prompt);

        $response = spin(
            callback: fn () => $session->sendAndWait($prompt),
            message: 'Thinking...',
        );

        note($response->content());
    }, config: $config);
})->purpose('Copilot MCP testing command');

// vendor/bin/testbench copilot:ask-user
Artisan::command('copilot:ask-user', function () {
    $config = new SessionConfig(
        onUserInputRequest: function (UserInputRequest $request, array $invocation) {
            dump('User input requested:', $request->toArray());

            if (! empty($request->choices)) {
                $answer = select(
                    label: $request->question,
                    options: $request->choices,
                );
            } else {
                $answer = text(
                    label: $request->question,
                    required: true,
                );
            }

            return new UserInputResponse(
                answer: $answer,
                wasFreeform: empty($request->choices),
            );
        },
    );

    Copilot::start(function (CopilotSession $session) {
        info('Starting Copilot with ask_user: '.$session->id());

        // ask_userツールを使うようにプロンプトで指示
        $prompt = 'Use the ask_user tool to ask me what programming language I prefer. '.
            'Provide choices: PHP, Python, JavaScript, Go. '.
            'Then tell me something interesting about the language I choose.';

        warning($prompt);

        $response = spin(
            callback: fn () => $session->sendAndWait($prompt),
            message: 'Thinking...',
        );

        note($response->content());
    }, config: $config);
})->purpose('Copilot ask_user (user input) testing command');

// vendor/bin/testbench copilot:hooks
Artisan::command('copilot:hooks', function () {
    $config = new SessionConfig(
        hooks: new SessionHooks(
            onPreToolUse: function (mixed $input, array $invocation) {
                info('[Hook] Pre-tool-use: '.($input['toolName'] ?? 'unknown'));
                dump('Input:', $input);

                // ツール実行を許可
                return new PreToolUseHookOutput(
                    permissionDecision: 'allow',
                );
            },
            onPostToolUse: function (mixed $input, array $invocation) {
                info('[Hook] Post-tool-use: '.($input['toolName'] ?? 'unknown'));

                return null; // 変更なし
            },
            onUserPromptSubmitted: function (mixed $input, array $invocation) {
                info('[Hook] User prompt submitted');
                dump('Prompt:', $input['prompt'] ?? '');

                return null;
            },
            onSessionStart: function (mixed $input, array $invocation) {
                info('[Hook] Session started: '.($input['source'] ?? 'unknown'));

                return null;
            },
            onSessionEnd: function (mixed $input, array $invocation) {
                info('[Hook] Session ended: '.($input['reason'] ?? 'unknown'));

                return null;
            },
            onErrorOccurred: function (mixed $input, array $invocation) {
                error('[Hook] Error occurred: '.($input['error'] ?? 'unknown'));

                return null;
            },
        ),
    );

    Copilot::start(function (CopilotSession $session) {
        info('Starting Copilot with hooks: '.$session->id());

        $prompt = 'What is 2 + 2? Just give me the number.';

        warning($prompt);

        $response = spin(
            callback: fn () => $session->sendAndWait($prompt),
            message: 'Thinking...',
        );

        note($response->content());
    }, config: $config);
})->purpose('Copilot hooks testing command');

// vendor/bin/testbench copilot:streaming
// vendor/bin/testbench copilot:streaming --resume=
Artisan::command('copilot:streaming {--resume=}', function () {
    $config = new SessionConfig(
        streaming: true,
    );

    Copilot::start(function (CopilotSession $session) {
        info('Starting Copilot with streaming: '.$session->id());

        $session->on(function (SessionEvent $event): void {
            if ($event->isAssistantMessageDelta()) {
                // deltaで小分けにされたメッセージが届くので改行など何も追加せずにそのまま表示。流暢な表示が実現できる。
                echo $event->deltaContent();
            }
        });

        while (true) {
            $prompt = text(
                label: 'Enter your prompt',
                placeholder: 'Ask me anything...',
                required: true,
                hint: 'Ctrl+C to exit',
            );

            // streaming=trueの場合はspinは使わない。delta間で余計なスピナーやメッセージを表示してしまう。
            $session->sendAndWait($prompt);
        }
    }, config: $config, resume: $this->option('resume'));
})->purpose('Copilot streaming');
