<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Metadata about a session.
 */
readonly class SessionMetadata implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $startTime,
        public string $modifiedTime,
        public ?string $summary = null,
        public bool $isRemote = false,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['sessionId'],
            startTime: $data['startTime'],
            modifiedTime: $data['modifiedTime'],
            summary: $data['summary'] ?? null,
            isRemote: $data['isRemote'] ?? false,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'startTime' => $this->startTime,
            'modifiedTime' => $this->modifiedTime,
            'summary' => $this->summary,
            'isRemote' => $this->isRemote,
        ], fn ($value) => $value !== null);
    }
}
