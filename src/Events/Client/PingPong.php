<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\Client;

use Illuminate\Foundation\Events\Dispatchable;
use Revolution\Copilot\Client;

class PingPong
{
    use Dispatchable;

    public function __construct(public array $response) {}
}
