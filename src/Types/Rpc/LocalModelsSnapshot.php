<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Snapshot and transient notifications for one connection-owned manager.
 *
 * @experimental
 */
readonly class LocalModelsSnapshot implements Arrayable
{
    /**
     * @param  array<LocalModelsEvent>  $events
     */
    public function __construct(
        public int $revision,
        public LocalModelsView $state,
        public array $events,
    ) {}

    public static function fromArray(array $data): static
    {
        $state = $data['state'] ?? [];

        return new static(
            revision: (int) ($data['revision'] ?? 0),
            state: $state instanceof LocalModelsView
                ? $state
                : LocalModelsView::fromArray(is_array($state) ? $state : []),
            events: array_map(
                static fn ($event) => $event instanceof LocalModelsEvent
                    ? $event
                    : LocalModelsEvent::fromArray(is_array($event) ? $event : []),
                $data['events'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'revision' => $this->revision,
            'state' => $this->state->toArray(),
            'events' => array_map(
                static fn (LocalModelsEvent $event) => $event->toArray(),
                $this->events,
            ),
        ];
    }
}
