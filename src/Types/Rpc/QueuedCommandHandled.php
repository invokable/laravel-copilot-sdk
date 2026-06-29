<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates the queued command was handled.
 */
readonly class QueuedCommandHandled implements Arrayable
{
    /**
     * @param  bool  $handled  The command was handled
     * @param  ?bool  $stopProcessingQueue  If true, stop processing remaining queued items
     */
    public function __construct(
        public bool $handled,
        public ?bool $stopProcessingQueue = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            handled: Arr::boolean($data, 'handled', true),
            stopProcessingQueue: $data['stopProcessingQueue'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'handled' => $this->handled,
            'stopProcessingQueue' => $this->stopProcessingQueue,
        ], fn ($v) => $v !== null);
    }
}
