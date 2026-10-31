<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\DiagnosticCursorStatus;

/**
 * One cursor-addressed page of retained lifecycle diagnostic events.
 *
 * @internal
 */
readonly class LifecycleDiagnosticsReadResult implements Arrayable
{
    /**
     * @param  LifecycleDiagnosticEvent[]  $events
     */
    public function __construct(
        public array $events,
        public string $cursor,
        public DiagnosticCursorStatus|string $cursorStatus,
        public bool $hasMore,
        public ?int $droppedCount = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $cursorStatus = Arr::string($data, 'cursorStatus', '');

        return new self(
            events: array_map(
                fn (array $event) => LifecycleDiagnosticEvent::fromArray($event),
                $data['events'] ?? [],
            ),
            cursor: Arr::string($data, 'cursor', ''),
            cursorStatus: DiagnosticCursorStatus::tryFrom($cursorStatus) ?? $cursorStatus,
            hasMore: Arr::boolean($data, 'hasMore', false),
            droppedCount: isset($data['droppedCount']) ? Arr::integer($data, 'droppedCount') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'events' => array_map(fn (LifecycleDiagnosticEvent $event) => $event->toArray(), $this->events),
            'cursor' => $this->cursor,
            'cursorStatus' => $this->cursorStatus instanceof DiagnosticCursorStatus
                ? $this->cursorStatus->value
                : $this->cursorStatus,
            ...($this->droppedCount === null ? [] : ['droppedCount' => $this->droppedCount]),
            'hasMore' => $this->hasMore,
        ];
    }
}
