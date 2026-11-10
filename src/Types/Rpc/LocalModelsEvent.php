<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A transient presentation update.
 *
 * @experimental
 */
readonly class LocalModelsEvent implements Arrayable
{
    public function __construct(
        public int $revision,
        public string $type,
        public bool $requiresDevAccess,
        public ?LocalModelTimelineMessage $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $message = $data['message'] ?? null;

        return new self(
            revision: (int) ($data['revision'] ?? 0),
            type: $data['type'] ?? '',
            requiresDevAccess: $data['requiresDevAccess'] ?? false,
            message: $message instanceof LocalModelTimelineMessage
                ? $message
                : (is_array($message) ? LocalModelTimelineMessage::fromArray($message) : null),
        );
    }

    public function toArray(): array
    {
        return [
            'revision' => $this->revision,
            'type' => $this->type,
            'requiresDevAccess' => $this->requiresDevAccess,
            ...array_filter([
                'message' => $this->message?->toArray(),
            ], static fn ($value) => $value !== null),
        ];
    }
}
