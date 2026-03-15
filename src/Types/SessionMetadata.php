<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Metadata about a session.
 */
readonly class SessionMetadata implements Arrayable
{
    /**
     * @param  string  $sessionId  Unique identifier of the session
     * @param  string  $startTime  ISO 8601 timestamp when the session was created
     * @param  string  $modifiedTime  ISO 8601 timestamp when the session was last modified
     * @param  ?string  $summary  Session summary
     * @param  bool  $isRemote  Whether this is a remote session
     * @param  ?SessionContext  $context  Working directory context (cwd, git info) from session creation
     */
    public function __construct(
        public string $sessionId,
        public string $startTime,
        public string $modifiedTime,
        public ?string $summary = null,
        public bool $isRemote = false,
        public ?SessionContext $context = null,
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
            context: isset($data['context']) ? SessionContext::fromArray($data['context']) : null,
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
            'context' => $this->context?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
