<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to publish an update for a client-owned task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksUpdateRequest implements Arrayable
{
    public function __construct(
        public string $id,
        public int $sequence,
        public TaskClientUpdate|array $update,
    ) {}

    public static function fromArray(array $data): self
    {
        $update = $data['update'] ?? [];

        return new self(
            id: Arr::string($data, 'id', ''),
            sequence: Arr::integer($data, 'sequence', 0),
            update: $update instanceof TaskClientUpdate
                ? $update
                : TaskClientUpdate::fromArray($update),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sequence' => $this->sequence,
            'update' => $this->update instanceof TaskClientUpdate
                ? $this->update->toArray()
                : $this->update,
        ];
    }
}
