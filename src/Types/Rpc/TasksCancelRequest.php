<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to cancel a task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksCancelRequest implements Arrayable
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
            id: Arr::string($data, 'id', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
