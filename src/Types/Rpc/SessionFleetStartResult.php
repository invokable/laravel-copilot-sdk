<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of starting fleet mode.
 */
readonly class SessionFleetStartResult implements Arrayable
{
    public function __construct(
        /** Whether fleet mode was successfully activated */
        public bool $started,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            started: $data['started'],
        );
    }

    public function toArray(): array
    {
        return [
            'started' => $this->started,
        ];
    }
}
