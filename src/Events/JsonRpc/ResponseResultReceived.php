<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\JsonRpc;

use Illuminate\Foundation\Events\Dispatchable;

class ResponseResultReceived
{
    use Dispatchable;

    public function __construct(public string $requestId, public mixed $result) {}
}
