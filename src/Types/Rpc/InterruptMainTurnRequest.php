<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for interrupting the current main agent turn.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class InterruptMainTurnRequest implements Arrayable
{
    public function __construct(
        public ?bool $flushQueued = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            flushQueued: $data['flushQueued'] ?? null,
        );
    }

    public function toArray(): array
    {
        return $this->flushQueued === null
            ? []
            : ['flushQueued' => $this->flushQueued];
    }
}
