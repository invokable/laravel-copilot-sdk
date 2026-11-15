<?php

declare(strict_types=1);

use Revolution\Copilot\Events\Process\ProcessStarted;
use Revolution\Copilot\Process\ProcessManager;

describe('ProcessStarted', function () {
    it('stores process manager reference', function () {
        $process = Mockery::mock(ProcessManager::class);
        $event = new ProcessStarted($process);

        expect($event->process)->toBe($process);
    });
});
