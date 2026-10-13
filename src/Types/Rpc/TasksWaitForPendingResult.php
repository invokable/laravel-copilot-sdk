<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of waiting for pending background tasks.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksWaitForPendingResult implements Arrayable
{
    public static function fromArray(array $data): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
