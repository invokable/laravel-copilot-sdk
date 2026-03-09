<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result from logging a message to the session timeline.
 */
readonly class SessionLogResult implements Arrayable
{
    public function __construct(
        /** The unique identifier of the emitted session event */
        public string $eventId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventId: $data['eventId'],
        );
    }

    public function toArray(): array
    {
        return [
            'eventId' => $this->eventId,
        ];
    }
}
