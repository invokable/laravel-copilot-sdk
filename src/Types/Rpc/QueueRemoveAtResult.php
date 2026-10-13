<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether a queued item was removed.
 */
readonly class QueueRemoveAtResult implements Arrayable
{
    /**
     * @param  bool  $removed  Whether the queued item was removed.
     */
    public function __construct(
        public bool $removed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            removed: Arr::boolean($data, 'removed', false),
        );
    }

    public function toArray(): array
    {
        return [
            'removed' => $this->removed,
        ];
    }
}
