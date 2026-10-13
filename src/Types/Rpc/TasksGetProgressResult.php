<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of fetching progress for a tracked task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksGetProgressResult implements Arrayable
{
    public function __construct(
        public TaskAgentProgress|TaskClientProgress|TaskShellProgress|null $progress = null,
        private bool $includeProgress = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            progress: TaskProgress::fromArray($data['progress'] ?? null),
            includeProgress: array_key_exists('progress', $data),
        );
    }

    public function toArray(): array
    {
        if (! $this->includeProgress) {
            return [];
        }

        return [
            'progress' => TaskProgress::toArray($this->progress),
        ];
    }
}
