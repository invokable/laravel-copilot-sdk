<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A single dependency edge read from the session SQL `todo_deps` table, indicating
 * that one todo must complete before another.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class PlanSqlTodoDependency implements Arrayable
{
    /**
     * @param  string  $dependsOn  ID of the todo it depends on.
     * @param  string  $todoId  ID of the todo that has the dependency.
     */
    public function __construct(
        public string $dependsOn,
        public string $todoId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            dependsOn: Arr::string($data, 'dependsOn', ''),
            todoId: Arr::string($data, 'todoId', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'dependsOn' => $this->dependsOn,
            'todoId' => $this->todoId,
        ];
    }
}
