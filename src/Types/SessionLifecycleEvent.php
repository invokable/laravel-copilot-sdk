<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionLifecycleEventType;

/**
 * Session lifecycle event notification.
 *
 * Sent when sessions are created, deleted, updated, or change foreground/background state.
 *
 * @implements Arrayable<string, mixed>
 */
readonly class SessionLifecycleEvent implements Arrayable
{
    public function __construct(
        /**
         * Type of lifecycle event.
         */
        public SessionLifecycleEventType $type,

        /**
         * ID of the session this event relates to.
         */
        public string $sessionId,

        /**
         * Session metadata (not included for deleted sessions).
         */
        public ?SessionLifecycleEventMetadata $metadata = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: SessionLifecycleEventType::from($data['type']),
            sessionId: $data['sessionId'],
            metadata: isset($data['metadata']) ? SessionLifecycleEventMetadata::fromArray($data['metadata']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'sessionId' => $this->sessionId,
            'metadata' => $this->metadata?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
