<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of a shell kill request.
 */
readonly class SessionShellKillResult implements Arrayable
{
    /**
     * @param  bool  $killed  Whether the signal was sent successfully
     */
    public function __construct(
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
