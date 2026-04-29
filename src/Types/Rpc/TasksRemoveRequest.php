<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to remove a completed or cancelled task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksRemoveRequest implements Arrayable
{
    /**
     * @param  string  $id  Task identifier
     */
    public function __construct(
        public string $id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
