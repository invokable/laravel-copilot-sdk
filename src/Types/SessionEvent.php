<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Dumpable;
use Illuminate\Support\Traits\InteractsWithData;
use Illuminate\Support\Traits\Tappable;
use Revolution\Copilot\Enums\SessionEventType;
use Throwable;

/**
 * Represents a session event from the Copilot CLI.
 */
readonly class SessionEvent implements Arrayable, Jsonable
{
    use Conditionable;
    use Dumpable;
    use InteractsWithData;
    use Tappable;

    /**
     * @param  string  $id  Unique event identifier (UUID v4), generated when the event is emitted
     * @param  string  $timestamp  ISO 8601 timestamp when the event was created
     * @param  ?string  $parentId  ID of the chronologically preceding event in the session, forming a linked chain. Null for the first event.
     * @param  SessionEventType  $type  Type of the session event
     * @param  array  $data  Event data payload specific to the event type
     * @param  bool  $ephemeral  When true, the event is transient and not persisted to the session event log on disk
     * @param  ?string  $agentId  Sub-agent instance identifier. Absent for events from the root/main agent and session-level events.
     * @param  ?Throwable  $exception  Exception associated with this event, if any
     */
    public function __construct(
        public string $id,
        public string $timestamp,
        public ?string $parentId,
        public SessionEventType $type,
        public array $data,
        public bool $ephemeral = false,
        public ?string $agentId = null,
        protected ?Throwable $exception = null,
    ) {
        //
    }

    /**
     * Create from array data.
     */
    public static function fromArray(array $event): self
    {
        return new self(
            id: $event['id'] ?? '',
            timestamp: $event['timestamp'] ?? '',
            parentId: $event['parentId'] ?? null,
            type: SessionEventType::tryFrom($event['type'] ?? '') ?? SessionEventType::SESSION_INFO,
            data: $event['data'] ?? [],
            ephemeral: $event['ephemeral'] ?? false,
            agentId: $event['agentId'] ?? null,
        );
    }

    /**
     * Check if this is an assistant message event.
     */
    public function isAssistantMessage(): bool
    {
        return $this->type === SessionEventType::ASSISTANT_MESSAGE;
    }

    /**
     * Check if this is an assistant message delta event.
     */
    public function isAssistantMessageDelta(): bool
    {
        return $this->type === SessionEventType::ASSISTANT_MESSAGE_DELTA;
    }

    /**
     * Check if this is a user message event.
     */
    public function isUserMessage(): bool
    {
        return $this->type === SessionEventType::USER_MESSAGE;
    }

    public function isAssistantReasoning(): bool
    {
        return $this->type === SessionEventType::ASSISTANT_REASONING;
    }

    public function isAssistantReasoningDelta(): bool
    {
        return $this->type === SessionEventType::ASSISTANT_REASONING_DELTA;
    }

    /**
     * Check if this is a session idle event.
     */
    public function isIdle(): bool
    {
        return $this->type === SessionEventType::SESSION_IDLE;
    }

    /**
     * Check if this event matches the given type.
     */
    public function is(SessionEventType $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Check if this is an error event.
     */
    public function failed(): bool
    {
        return $this->type === SessionEventType::SESSION_ERROR;
    }

    /**
     * @throws Throwable
     */
    public function throw(): static
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this;
    }

    /**
     * Check if this event was successful.
     */
    public function successful(): bool
    {
        return ! $this->failed();
    }

    /**
     * Retrieve all data from the instance.
     *
     * @param  mixed  $keys
     */
    public function all($keys = null): array
    {
        if (is_null($keys)) {
            return $this->data;
        }

        $result = [];
        foreach ((array) $keys as $key) {
            $result[$key] = $this->data[$key] ?? null;
        }

        return $result;
    }

    /**
     * Retrieve data from the instance.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     */
    protected function data($key = null, $default = null): mixed
    {
        if (is_null($key)) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * Get the content from assistant message data.
     */
    public function content(?string $default = null): ?string
    {
        return $this->data('content', $default);
    }

    public function deltaContent(?string $default = null): ?string
    {
        return $this->data('deltaContent', $default);
    }

    /**
     * Get the error message from error data.
     */
    public function errorMessage(?string $default = null): ?string
    {
        return $this->data('message', $default);
    }

    /**
     * Get the event type as string.
     */
    public function type(): string
    {
        return $this->type->value;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'parentId' => $this->parentId,
            'type' => $this->type->value,
            'data' => $this->data,
            'ephemeral' => $this->ephemeral,
            'agentId' => $this->agentId,
        ];
    }

    /**
     * Convert to JSON.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Broadcast the session event using the queue.
     */
    public function broadcast(Channel|array $channels, bool $now = false): void
    {
        Broadcast::on($channels)
            ->as($this->type())
            ->with($this->toArray())
            ->{$now ? 'sendNow' : 'send'}();
    }

    /**
     * Broadcast the session event immediately.
     */
    public function broadcastNow(Channel|array $channels): void
    {
        $this->broadcast($channels, now: true);
    }

    /**
     * When converted to string, return the content.
     */
    public function __toString(): string
    {
        return $this->content('');
    }
}
