<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Notification that an AHP session handoff has ended. */
readonly class HostSessionReleasedNotification implements Arrayable
{
    public function __construct(
        public string $hostId,
        public string $handoffId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            handoffId: $data['handoffId'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'hostId' => $this->hostId,
            'handoffId' => $this->handoffId,
        ];
    }
}
