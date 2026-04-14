<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of setting session mode.
 *
 * @deprecated The session.mode.set RPC now returns void.
 *             PendingMode::set() no longer returns a result.
 */
readonly class SessionModeSetResult implements Arrayable
{
    /**
     * @param  string  $mode  The agent mode after switching.
     */
    public function __construct(
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
