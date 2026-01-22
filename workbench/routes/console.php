<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Session;

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
    Copilot::start(function (Session $session) {
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
                }
            }

            outro('You can continue the conversation below.');
        }

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

            note($response->getContent());
        }
    });
})->purpose('Interactive chat session with Copilot CLI SDK');
