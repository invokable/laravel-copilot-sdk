<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionEventType;

/**
 * Represents a session event from the Copilot CLI.
 */
readonly class SessionEvent implements Arrayable
{
    public function __construct(
        public string $id,
        public string $timestamp,
        public ?string $parentId,
        public SessionEventType $type,
        public array $data,
        public bool $ephemeral = false,
    ) {}

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
     * Check if this is a user message event.
     */
    public function isUserMessage(): bool
    {
        return $this->type === SessionEventType::USER_MESSAGE;
    }

    /**
     * Check if this is a session idle event.
     */
    public function isIdle(): bool
    {
        return $this->type === SessionEventType::SESSION_IDLE;
    }

    /**
     * Check if this is an error event.
     */
    public function failed(): bool
    {
        return $this->type === SessionEventType::SESSION_ERROR;
    }

    /**
     * Get the content from assistant message data.
     */
    public function content(): ?string
    {
        return $this->data['content'] ?? null;
    }

    /**
     * Get the error message from error data.
     */
    public function errorMessage(): ?string
    {
        return $this->data['message'] ?? null;
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
        ];
    }

    public function __toString(): string
    {
        return $this->content() ?? '';
    }
}
