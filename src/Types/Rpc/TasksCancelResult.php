<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of cancelling a task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksCancelResult implements Arrayable
{
    /**
     * @param  bool  $cancelled  Whether the task was successfully cancelled
     */
    public function __construct(
        public bool $cancelled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cancelled: (bool) ($data['cancelled'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'cancelled' => $this->cancelled,
        ];
    }
}
