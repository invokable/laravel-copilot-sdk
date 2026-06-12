<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Todo rows read from the session SQL database. Empty when no session database is available.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class PlanReadSqlTodosResult implements Arrayable
{
    /**
     * @param  PlanSqlTodosRow[]  $rows  Rows from the session SQL todos table, ordered by creation time and id.
     */
    public function __construct(
        public array $rows,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            rows: array_map(
                fn (array $row) => PlanSqlTodosRow::fromArray($row),
                $data['rows'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'rows' => array_map(fn (PlanSqlTodosRow $row) => $row->toArray(), $this->rows),
        ];
    }
}
