<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether a user-facing pending item was removed.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class QueueRemoveMostRecentResult implements Arrayable
{
    /**
     * @param  bool  $removed  True if a user-facing pending item was removed (LIFO across both queues); false when no removable items remained
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
