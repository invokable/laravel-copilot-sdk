<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to withdraw an application publication from a host catalog. */
readonly class HostUnpublishSessionRequest implements Arrayable
{
    public function __construct(
        public string $hostId,
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            sessionId: $data['sessionId'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'hostId' => $this->hostId,
            'sessionId' => $this->sessionId,
        ];
    }
}
