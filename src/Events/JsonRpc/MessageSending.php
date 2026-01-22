<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\JsonRpc;

use Illuminate\Foundation\Events\Dispatchable;
use Revolution\Copilot\JsonRpc\JsonRpcMessage;

class MessageSending
{
    use Dispatchable;

    public function __construct(public JsonRpcMessage $message) {}
}
