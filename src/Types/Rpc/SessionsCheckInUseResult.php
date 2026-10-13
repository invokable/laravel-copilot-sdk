<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Session IDs currently held by another running process.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsCheckInUseResult implements Arrayable
{
    /**
     * @param  array<string>  $inUse
     */
    public function __construct(
        public array $inUse,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(inUse: $data['inUse'] ?? []);
    }

    public function toArray(): array
    {
        return ['inUse' => $this->inUse];
    }
}
