<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\Client;

use Illuminate\Foundation\Events\Dispatchable;

class ToolCall
{
    use Dispatchable;

    public function __construct(public array $arguments, public array $invocation, public mixed $result) {}
}
