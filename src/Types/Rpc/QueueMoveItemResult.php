<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether the queued item changed position.
 */
readonly class QueueMoveItemResult implements Arrayable
{
    /**
     * @param  bool  $changed  Whether the item changed position.
     */
    public function __construct(
        public bool $changed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            changed: Arr::boolean($data, 'changed', false),
        );
    }

    public function toArray(): array
    {
        return [
            'changed' => $this->changed,
        ];
    }
}
