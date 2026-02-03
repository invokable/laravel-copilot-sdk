<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Metadata for session lifecycle events.
 *
 * @implements Arrayable<string, mixed>
 */
readonly class SessionLifecycleEventMetadata implements Arrayable
{
    public function __construct(
        public string $startTime,
        public string $modifiedTime,
        public ?string $summary = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            startTime: $data['startTime'],
            modifiedTime: $data['modifiedTime'],
            summary: $data['summary'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'startTime' => $this->startTime,
            'modifiedTime' => $this->modifiedTime,
            'summary' => $this->summary,
        ], fn ($value) => $value !== null);
    }
}
