<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of a shell kill request.
 */
readonly class SessionShellKillResult implements Arrayable
{
    public function __construct(
        /**
         * Whether the signal was sent successfully.
         */
        public bool $killed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            killed: (bool) $data['killed'],
        );
    }

    public function toArray(): array
    {
        return [
            'killed' => $this->killed,
        ];
    }
}
