<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Revolution\Copilot\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// vendor/bin/testbench copilot:ping
Artisan::command('copilot:ping', function () {
    Copilot::start(function (CopilotSession $session) {
        $this->info($session->id());
    });
    //    $client = Copilot::getClient();
    //    $client->start();
    //    dump($client->ping());
});
