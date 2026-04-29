<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of removing a task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksRemoveResult implements Arrayable
{
    /**
     * @param  bool  $removed  Whether the task was removed. Returns false if the task does not exist or is still running/idle (cancel it first).
     */
    public function __construct(
        public bool $removed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            removed: (bool) ($data['removed'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'removed' => $this->removed,
        ];
    }
}
