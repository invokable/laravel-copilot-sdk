<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\EventsAgentScope;

/**
 * Parameters for reading session events from the event log.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EventLogReadRequest implements Arrayable
{
    /**
     * @param  ?string  $cursor  Opaque cursor returned by a previous read
     * @param  ?int  $max  Maximum number of events to return in this batch
     * @param  ?int  $waitMs  Milliseconds to wait for new events at the tail
     * @param  array<string>|string|null  $types  Either "*" or a non-empty list of event types
     * @param  EventsAgentScope|string|null  $agentScope  Agent-scope filter for returned events
     */
    public function __construct(
        public ?string $cursor = null,
        public ?int $max = null,
        public ?int $waitMs = null,
        public array|string|null $types = null,
        public EventsAgentScope|string|null $agentScope = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $agentScope = $data['agentScope'] ?? null;
        if (is_string($agentScope)) {
            $agentScope = EventsAgentScope::tryFrom($agentScope) ?? $agentScope;
        }

        return new self(
            cursor: $data['cursor'] ?? null,
            max: $data['max'] ?? null,
            waitMs: $data['waitMs'] ?? null,
            types: $data['types'] ?? null,
            agentScope: $agentScope,
        );
    }

    public function toArray(): array
    {
        $agentScope = $this->agentScope instanceof EventsAgentScope
            ? $this->agentScope->value
            : $this->agentScope;

        return array_filter([
            'cursor' => $this->cursor,
            'max' => $this->max,
            'waitMs' => $this->waitMs,
            'types' => $this->types,
            'agentScope' => $agentScope,
        ], fn ($value): bool => $value !== null);
    }
}
