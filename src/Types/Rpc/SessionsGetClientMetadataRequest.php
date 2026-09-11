<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Bounded batch request for client-owned metadata from persisted local sessions.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsGetClientMetadataRequest implements Arrayable
{
    /**
     * @param  array<string>  $sessionIds  Session IDs to inspect. Results preserve this order.
     * @param  ?array  $keys  Case-sensitive keys to project from each valid bag. Omit to return every entry.
     */
    public function __construct(
        public array $sessionIds,
        public ?array $keys = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionIds: $data['sessionIds'] ?? [],
            keys: $data['keys'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionIds' => $this->sessionIds,
            'keys' => $this->keys,
        ], fn ($value) => $value !== null);
    }
}
