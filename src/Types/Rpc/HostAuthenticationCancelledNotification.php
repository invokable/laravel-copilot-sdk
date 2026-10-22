<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Notification that an in-flight custom authentication validation was cancelled. */
readonly class HostAuthenticationCancelledNotification implements Arrayable
{
    public function __construct(
        public string $hostId,
        public string $requestId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            requestId: $data['requestId'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'hostId' => $this->hostId,
            'requestId' => $this->requestId,
        ];
    }
}
