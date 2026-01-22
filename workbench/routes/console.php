<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
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

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// vendor/bin/testbench copilot:ping
Artisan::command('copilot:ping', function () {
    Copilot::start(function (CopilotSession $session) {
        $this->info('Session ID: '.$session->id());
        $this->info(json_encode(Copilot::getClient()->ping()));
    });
});

// vendor/bin/testbench copilot:chat
// vendor/bin/testbench copilot:chat --resume
Artisan::command('copilot:chat {--resume}', function () {
    $config = new SessionConfig(
        onPermissionRequest: function (array $request, array $invocation) {
            dump($request, $invocation);
            $confirm = confirm(
                label: 'Do you accept the requested permissions?',
            );
            if ($confirm) {
                return ['kind' => 'approved'];
            } else {
                return ['kind' => 'denied-interactively-by-user'];
            }
        },
    );

    Copilot::start(function (CopilotSession $session) {
        info('Starting Copilot chat session: '.$session->id());

        if ($this->option('resume')) {
            $sessions = collect(Copilot::getClient()->listSessions())
                ->mapWithKeys(function ($session) {
                    return [$session['sessionId'] => $session['summary'] ?? ''];
                })
                ->toArray();

            $session_id = select(
                label: 'What session do you want to resume?',
                options: $sessions,
            );

            $session->destroy();
            $session = Copilot::getClient()->resumeSession($session_id);

            intro("Resumed previous session: $session_id. Here are the past assistant messages");

            $messages = $session->getMessages();
            foreach ($messages as $message) {
                if ($message->isAssistantMessage()) {
                    note($message->getContent());
                } elseif ($message->isUserMessage()) {
                    info($message->getContent());
                }
            }

            outro('You can continue the conversation below.');
        }

        // sendAndWaitは最後のアシスタントメッセージのみ返す。
        // 途中のメッセージも表示したい場合はハンドラを追加。
        $session->on(function (SessionEvent $event): void {
            if ($event->isAssistantMessage()) {
                note($event->getContent());
            } elseif ($event->isError()) {
                error($event->getErrorMessage() ?? 'Unknown error');
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
                message: 'Waiting for Copilot response...',
            );

            // 上のonハンドラで表示してるのでsendAndWaitからの最終メッセージの表示は不要。
            // 追加のハンドラを使わず最後のメッセージのみ使う場合はここで表示する。
            // note($response->getContent());
        }
    }, config: $config);
})->purpose('Interactive chat session with Copilot CLI SDK');
