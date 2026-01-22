<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\PermissionRequestKind;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// vendor/bin/testbench copilot:ping
Artisan::command('copilot:ping', function () {
    Copilot::start(function (CopilotSession $session) {
        $this->info('Session ID: '.$session->id());
        $this->info(json_encode(Copilot::client()->ping()));
    });
});

// vendor/bin/testbench copilot:chat
// vendor/bin/testbench copilot:chat --resume
Artisan::command('copilot:chat {--resume}', function () {
    $config = new SessionConfig(
        // availableTools: [],
        onPermissionRequest: function (array $request, array $invocation) {
            // dump($request);
            $confirm = confirm(
                label: 'Do you accept the requested permissions?',
            );

            if ($confirm) {
                return PermissionRequestKind::approved();
            } else {
                return PermissionRequestKind::deniedInteractivelyByUser();
            }
        },
    );

    Copilot::start(function (CopilotSession $session) use ($config) {
        info('Starting Copilot chat session: '.$session->id());

        if ($this->option('resume')) {
            $sessions = collect(Copilot::client()->listSessions())
                ->mapWithKeys(function ($session) {
                    return [$session['sessionId'] => $session['summary'] ?? ''];
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
            } elseif ($event->isError()) {
                error($event->getErrorMessage() ?? 'Unknown error');
            } else {
                info('Event: '.$event->type->value);
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
})->purpose('Copilot Tools testing command');
