<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to shut down a session.
 *
 * @experimental
 */
readonly class ShutdownRequest implements Arrayable
{
    public function __construct(
        public ?string $type = null,
        public ?string $reason = null,
        public ?bool $detachSessionEndHooks = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? null,
            reason: $data['reason'] ?? null,
            detachSessionEndHooks: $data['detachSessionEndHooks'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'reason' => $this->reason,
            'detachSessionEndHooks' => $this->detachSessionEndHooks,
        ], fn ($value) => $value !== null);
    }
}
