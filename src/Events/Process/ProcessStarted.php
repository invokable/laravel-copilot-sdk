<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\Process;

use Illuminate\Foundation\Events\Dispatchable;
use Revolution\Copilot\Process\ProcessManager;

class ProcessStarted
{
    use Dispatchable;

    public function __construct(public ProcessManager $process) {}
}
