<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Indicates the queued command was not handled.
 */
readonly class QueuedCommandNotHandled implements Arrayable
{
    /**
     * @param  bool  $handled  The command was not handled
     */
    public function __construct(
        public bool $handled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            handled: $data['handled'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'handled' => $this->handled,
        ];
    }
}
