<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\LogLevel;

/**
 * Parameters for logging a message to the session timeline.
 */
readonly class SessionLogParams implements Arrayable
{
    public function __construct(
        /** Human-readable message */
        public string $message,
        /** Log severity level. Determines how the message is displayed in the timeline. Defaults to "info". */
        public ?LogLevel $level = null,
        /** When true, the message is transient and not persisted to the session event log on disk */
        public ?bool $ephemeral = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'],
            level: isset($data['level']) ? LogLevel::from($data['level']) : null,
            ephemeral: $data['ephemeral'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'level' => $this->level?->value,
            'ephemeral' => $this->ephemeral,
        ], fn ($v) => $v !== null);
    }
}
