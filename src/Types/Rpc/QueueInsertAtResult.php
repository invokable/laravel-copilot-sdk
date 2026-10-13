<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Identifier of the inserted queue item.
 */
readonly class QueueInsertAtResult implements Arrayable
{
    /**
     * @param  string  $id  Identifier of the inserted queue item.
     */
    public function __construct(
        public string $id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
