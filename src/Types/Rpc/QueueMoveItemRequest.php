<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for moving a queued item to another visible position.
 */
readonly class QueueMoveItemRequest implements Arrayable
{
    /**
     * @param  string  $id  Identifier of the queued item to move.
     * @param  int  $toPosition  Zero-based destination position.
     */
    public function __construct(
        public string $id,
        public int $toPosition,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
            toPosition: Arr::integer($data, 'toPosition'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'toPosition' => $this->toPosition,
        ];
    }
}
