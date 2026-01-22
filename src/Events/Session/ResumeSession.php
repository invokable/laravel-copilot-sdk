<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\Session;

use Illuminate\Foundation\Events\Dispatchable;
use Revolution\Copilot\Session;

class ResumeSession
{
    use Dispatchable;

    public function __construct(
        public Session $session,
    ) {}
}
