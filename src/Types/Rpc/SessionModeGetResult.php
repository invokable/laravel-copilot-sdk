<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting session mode.
 */
readonly class SessionModeGetResult implements Arrayable
{
    public function __construct(
        /** The current agent mode. */
        public string $mode,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mode: $data['mode'],
        );
    }

    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
        ];
    }
}
