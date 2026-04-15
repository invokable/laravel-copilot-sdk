<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for truncating session history.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryTruncateRequest implements Arrayable
{
    /**
     * @param  string  $eventId  Event ID to truncate to. This event and all events after it are removed from the session.
     */
    public function __construct(
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
