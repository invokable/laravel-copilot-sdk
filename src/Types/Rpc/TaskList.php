<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * List of currently tracked tasks.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskList implements Arrayable
{
    /**
     * @param  array<TaskAgentInfo|TaskShellInfo>  $tasks
     */
    public function __construct(
        public array $tasks = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $tasks = array_map(
            fn (array $task) => ($task['type'] ?? '') === 'shell'
                ? TaskShellInfo::fromArray($task)
                : TaskAgentInfo::fromArray($task),
            $data['tasks'] ?? [],
        );

        return new self(tasks: $tasks);
    }

    public function toArray(): array
    {
        return [
            'tasks' => array_map(fn ($task) => $task->toArray(), $this->tasks),
        ];
    }
}
