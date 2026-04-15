<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for forking a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsForkRequest implements Arrayable
{
    /**
     * @param  string  $sessionId  Source session ID to fork from
     * @param  ?string  $toEventId  Optional event ID boundary. When provided, the fork includes only events before this ID (exclusive). When omitted, all events are included.
     */
    public function __construct(
        public string $sessionId,
        public ?string $toEventId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['sessionId'],
            toEventId: $data['toEventId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'toEventId' => $this->toEventId,
        ], fn ($v) => $v !== null);
    }
}
