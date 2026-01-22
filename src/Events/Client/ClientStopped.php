<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\Client;

use Illuminate\Foundation\Events\Dispatchable;
use Revolution\Copilot\Client;

class ClientStopped
{
    use Dispatchable;

    public function __construct(public Client $client) {}
}
