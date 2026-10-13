<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of registering or reclaiming a client-owned task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksRegisterResult implements Arrayable
{
    public function __construct(
        public TaskClientInfo $task,
        public bool $created,
        public bool $reclaimed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            task: TaskClientInfo::fromArray($data['task'] ?? []),
            created: Arr::boolean($data, 'created', false),
            reclaimed: Arr::boolean($data, 'reclaimed', false),
        );
    }

    public function toArray(): array
    {
        return [
            'task' => $this->task->toArray(),
            'created' => $this->created,
            'reclaimed' => $this->reclaimed,
        ];
    }
}
