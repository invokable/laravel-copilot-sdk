<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Session;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

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
// vendor/bin/testbench copilot:chat --resume={session_id}
Artisan::command('copilot:chat {--resume=}', function () {
    $resume = $this->option('resume');

    Copilot::start(function (Session $session) use ($resume) {
        info('Starting Copilot chat session: '.$session->id());

        if ($resume) {
            intro('Resumed previous session. Here are the past assistant messages:');

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
    }, resume: $resume);
})->purpose('Interactive chat session with Copilot CLI SDK');
