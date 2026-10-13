<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\EventsReadDirection;

/**
 * Pagination options for reading a persisted session event journal.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsReadPersistedEventsRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public ?string $cursor = null,
        public ?int $max = null,
        public EventsReadDirection|string|null $direction = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $direction = $data['direction'] ?? null;
        if (is_string($direction)) {
            $direction = EventsReadDirection::tryFrom($direction) ?? $direction;
        }

        return new self(
            sessionId: $data['sessionId'],
            cursor: $data['cursor'] ?? null,
            max: $data['max'] ?? null,
            direction: $direction,
        );
    }

    public function toArray(): array
    {
        $direction = $this->direction instanceof EventsReadDirection
            ? $this->direction->value
            : $this->direction;

        return array_filter([
            'sessionId' => $this->sessionId,
            'cursor' => $this->cursor,
            'max' => $this->max,
            'direction' => $direction,
        ], fn ($value): bool => $value !== null);
    }
}
