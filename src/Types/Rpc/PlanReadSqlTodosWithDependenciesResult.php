<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Todo rows + dependency edges read from the session SQL database.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class PlanReadSqlTodosWithDependenciesResult implements Arrayable
{
    /**
     * @param  PlanSqlTodosRow[]  $rows  Rows from the session SQL todos table, ordered by creation time and id.
     * @param  PlanSqlTodoDependency[]  $dependencies  Edges from the session SQL todo_deps table.
     */
    public function __construct(
        public array $rows,
        public array $dependencies,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            rows: array_map(
                fn (array $row) => PlanSqlTodosRow::fromArray($row),
                $data['rows'] ?? [],
            ),
            dependencies: array_map(
                fn (array $dep) => PlanSqlTodoDependency::fromArray($dep),
                $data['dependencies'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'rows' => array_map(fn (PlanSqlTodosRow $row) => $row->toArray(), $this->rows),
            'dependencies' => array_map(fn (PlanSqlTodoDependency $dep) => $dep->toArray(), $this->dependencies),
        ];
    }
}
