<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SessionHistoryImportConversationRequest implements Arrayable
{
    /** @param  array<HistoryImportConversationEvent|array>  $events */
    public function __construct(public array $events) {}

    public static function fromArray(array $data): self
    {
        return new self(events: array_map(
            fn (HistoryImportConversationEvent|array $event) => $event instanceof HistoryImportConversationEvent
                ? $event
                : HistoryImportConversationEvent::fromArray($event),
            array_values(Arr::array($data, 'events')),
        ));
    }

    public function toArray(): array
    {
        return ['events' => array_map(
            fn (HistoryImportConversationEvent|array $event) => ($event instanceof HistoryImportConversationEvent
                ? $event
                : HistoryImportConversationEvent::fromArray($event))->toArray(),
            array_values($this->events),
        )];
    }
}
