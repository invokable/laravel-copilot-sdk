<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A single row from the session SQL todos table.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class PlanSqlTodosRow implements Arrayable
{
    /**
     * @param  ?string  $description  Todo description.
     * @param  ?string  $id  Todo identifier.
     * @param  ?string  $status  Todo status.
     * @param  ?string  $title  Todo title.
     */
    public function __construct(
        public ?string $description = null,
        public ?string $id = null,
        public ?string $status = null,
        public ?string $title = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'] ?? null,
            id: $data['id'] ?? null,
            status: $data['status'] ?? null,
            title: $data['title'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'description' => $this->description,
            'id' => $this->id,
            'status' => $this->status,
            'title' => $this->title,
        ], fn ($v) => $v !== null);
    }
}
