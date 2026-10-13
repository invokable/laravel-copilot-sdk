<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting the first task eligible for background promotion.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksGetCurrentPromotableResult implements Arrayable
{
    public function __construct(
        public TaskAgentInfo|TaskClientInfo|TaskShellInfo|null $task = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            task: isset($data['task']) ? TaskInfo::fromArray($data['task']) : null,
        );
    }

    public function toArray(): array
    {
        return $this->task === null
            ? []
            : ['task' => TaskInfo::toArray($this->task)];
    }
}
