<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of cancelling a background history compaction.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryCancelBackgroundCompactionResult implements Arrayable
{
    /**
     * @param  bool  $cancelled  Whether the background compaction was cancelled
     */
    public function __construct(
        public bool $cancelled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cancelled: Arr::boolean($data, 'cancelled'),
        );
    }

    public function toArray(): array
    {
        return [
            'cancelled' => $this->cancelled,
        ];
    }
}
