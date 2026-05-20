<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Query results including rows, columns, and rows affected, or a filesystem error
 * if execution failed.
 */
readonly class SessionFsSqliteQueryResult implements Arrayable
{
    /**
     * @param  list<string>  $columns  Column names from the result set
     * @param  list<array<string, mixed>>  $rows  For SELECT: array of row objects. For others: empty array.
     * @param  int  $rowsAffected  Number of rows affected (for INSERT/UPDATE/DELETE)
     * @param  ?SessionFSError  $error  Filesystem error if execution failed
     * @param  ?float  $lastInsertRowid  Last inserted row ID (for INSERT)
     */
    public function __construct(
        public array $columns,
        public array $rows,
        public int $rowsAffected,
        public ?SessionFSError $error = null,
        public ?float $lastInsertRowid = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            columns: $data['columns'] ?? [],
            rows: $data['rows'] ?? [],
            rowsAffected: (int) ($data['rowsAffected'] ?? 0),
            error: isset($data['error']) ? SessionFSError::fromArray($data['error']) : null,
            lastInsertRowid: isset($data['lastInsertRowid']) ? (float) $data['lastInsertRowid'] : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'columns' => $this->columns,
            'rows' => $this->rows,
            'rowsAffected' => $this->rowsAffected,
        ];

        if ($this->error !== null) {
            $result['error'] = $this->error->toArray();
        }

        if ($this->lastInsertRowid !== null) {
            $result['lastInsertRowid'] = $this->lastInsertRowid;
        }

        return $result;
    }
}
