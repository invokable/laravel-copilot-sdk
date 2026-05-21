<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\EventsCursorStatus;
use Revolution\Copilot\Types\SessionEvent;

/**
 * Batch of events from a session event log read.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EventsReadResult implements Arrayable
{
    /**
     * @param  array<SessionEvent>  $events
     */
    public function __construct(
        public array $events,
        public string $cursor,
        public bool $hasMore,
        public EventsCursorStatus|string $cursorStatus,
    ) {}

    public static function fromArray(array $data): self
    {
        $cursorStatus = $data['cursorStatus'] ?? EventsCursorStatus::OK->value;
        if (is_string($cursorStatus)) {
            $cursorStatus = EventsCursorStatus::tryFrom($cursorStatus) ?? $cursorStatus;
        }

        return new self(
            events: array_map(
                fn (array $event) => SessionEvent::fromArray($event),
                $data['events'] ?? [],
            ),
            cursor: $data['cursor'] ?? '',
            hasMore: (bool) ($data['hasMore'] ?? false),
            cursorStatus: $cursorStatus,
        );
    }

    public function toArray(): array
    {
        $cursorStatus = $this->cursorStatus instanceof EventsCursorStatus
            ? $this->cursorStatus->value
            : $this->cursorStatus;

        return [
            'events' => array_map(fn (SessionEvent $event) => $event->toArray(), $this->events),
            'cursor' => $this->cursor,
            'hasMore' => $this->hasMore,
            'cursorStatus' => $cursorStatus,
        ];
    }
}
