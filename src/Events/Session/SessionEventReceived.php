<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\Session;

use Illuminate\Foundation\Events\Dispatchable;
use Revolution\Copilot\Types\SessionEvent;

class SessionEventReceived
{
    use Dispatchable;

    public function __construct(
        public string $sessionId,
        public SessionEvent $event,
    ) {}
}
