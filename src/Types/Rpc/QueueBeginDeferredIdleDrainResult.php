<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of beginning a deferred idle drain of the send queue.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class QueueBeginDeferredIdleDrainResult implements Arrayable
{
    /**
     * @param  bool  $shouldDrain  Whether the caller should proceed to drain the queue.
     */
    public function __construct(
        public bool $shouldDrain,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            shouldDrain: Arr::boolean($data, 'shouldDrain', false),
        );
    }

    public function toArray(): array
    {
        return [
            'shouldDrain' => $this->shouldDrain,
        ];
    }
}
