<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of the queued command execution.
 */
readonly class QueuedCommandResult implements Arrayable
{
    /**
     * @param  bool  $handled  The command was handled (or not handled)
     * @param  ?bool  $stopProcessingQueue  If true, stop processing remaining queued items
     */
    public function __construct(
        public bool $handled,
        public ?bool $stopProcessingQueue = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            handled: $data['handled'] ?? false,
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
