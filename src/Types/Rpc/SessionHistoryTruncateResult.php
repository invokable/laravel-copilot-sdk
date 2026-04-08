<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of session history truncation.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionHistoryTruncateResult implements Arrayable
{
    /**
     * @param  int  $eventsRemoved  Number of events that were removed
     */
    public function __construct(
        public int $eventsRemoved,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventsRemoved: $data['eventsRemoved'],
        );
    }

    public function toArray(): array
    {
        return [
            'eventsRemoved' => $this->eventsRemoved,
        ];
    }
}
