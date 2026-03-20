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
    /**
     * @param  string  $message  Human-readable message
     * @param  ?LogLevel  $level  Log severity level. Determines how the message is displayed in the timeline. Defaults to "info".
     * @param  ?bool  $ephemeral  When true, the message is transient and not persisted to the session event log on disk
     * @param  ?string  $url  Optional URL the user can open in their browser for more details
     */
    public function __construct(
        public string $message,
        public ?LogLevel $level = null,
        public ?bool $ephemeral = null,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'],
            level: isset($data['level']) ? LogLevel::from($data['level']) : null,
            ephemeral: $data['ephemeral'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'level' => $this->level?->value,
            'ephemeral' => $this->ephemeral,
            'url' => $this->url,
        ], fn ($v) => $v !== null);
    }
}
