<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to publish a resident session into a host catalog. */
readonly class HostPublishSessionRequest implements Arrayable
{
    public function __construct(
        public string $hostId,
        public string $sessionId,
        public ?bool $preferResident = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            sessionId: $data['sessionId'] ?? '',
            preferResident: $data['preferResident'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'hostId' => $this->hostId,
            'sessionId' => $this->sessionId,
            'preferResident' => $this->preferResident,
        ], fn ($value) => $value !== null);
    }
}
