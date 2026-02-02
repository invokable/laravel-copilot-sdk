<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/copilot', function () {
    return view('copilot');
});

Route::get('/copilot/sse', function () {
    return response()->stream(function () {
        Copilot::start(function (CopilotSession $session) {
            $session->on(function (SessionEvent $event) {
                if ($event->isAssistantMessageDelta()) {
                    echo "event: update\n";
                    echo 'data: '.$event->deltaContent()."\n\n";
                    ob_flush();
                    flush();
                }
            });

            $session->sendAndWait('Tell me something about Laravel.');
        }, config: new SessionConfig(model: 'gpt-5-mini', streaming: true));

        echo "event: update\n";
        echo "data: </stream>\n\n";
        ob_flush();
        flush();
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'X-Accel-Buffering' => 'no',
    ]);
});

Route::get('/copilot/stream', function () {
    return response()->eventStream(function () {
        yield from Copilot::stream(function (CopilotSession $session) {
            foreach ($session->sendAndStream('Tell me something about Laravel.') as $event) {
                if ($event->isAssistantMessageDelta()) {
                    yield $event->deltaContent();
                }
            }
        }, config: new SessionConfig(model: 'gpt-5-mini', streaming: true));
    });
});
