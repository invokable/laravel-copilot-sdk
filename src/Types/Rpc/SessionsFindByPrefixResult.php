<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of looking up a unique session ID by UUID prefix.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsFindByPrefixResult implements Arrayable
{
    public function __construct(
        public ?string $sessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(sessionId: $data['sessionId'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
        ], fn ($value): bool => $value !== null);
    }
}
