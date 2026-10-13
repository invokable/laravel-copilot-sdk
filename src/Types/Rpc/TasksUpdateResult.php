<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of publishing a client-owned task update.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksUpdateResult implements Arrayable
{
    public function __construct(
        public TaskClientInfo $task,
        public bool $applied,
        public bool $duplicate,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            task: TaskClientInfo::fromArray($data['task'] ?? []),
            applied: Arr::boolean($data, 'applied', false),
            duplicate: Arr::boolean($data, 'duplicate', false),
        );
    }

    public function toArray(): array
    {
        return [
            'task' => $this->task->toArray(),
            'applied' => $this->applied,
            'duplicate' => $this->duplicate,
        ];
    }
}
