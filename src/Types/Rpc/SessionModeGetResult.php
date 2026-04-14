<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting session mode.
 *
 * @deprecated The session.mode.get RPC now returns the mode string directly.
 *             Use the string return from PendingMode::get() instead.
 */
readonly class SessionModeGetResult implements Arrayable
{
    /**
     * @param  string  $mode  The current agent mode.
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
