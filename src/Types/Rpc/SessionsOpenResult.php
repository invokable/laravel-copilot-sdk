<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionsOpenStatus;

/**
 * Result of opening a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsOpenResult implements Arrayable
{
    /**
     * @param  string[]|null  $startupPrompts
     * @param  SessionsOpenProgress[]|null  $progress
     */
    public function __construct(
        public SessionsOpenStatus|string $status,
        public ?string $sessionId = null,
        public ?array $startupPrompts = null,
        public ?string $remoteSessionId = null,
        public ?array $metadata = null,
        public ?array $progress = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            status: SessionsOpenStatus::tryFrom((string) ($data['status'] ?? '')) ?? (string) ($data['status'] ?? ''),
            sessionId: $data['sessionId'] ?? null,
            startupPrompts: isset($data['startupPrompts']) ? array_values($data['startupPrompts']) : null,
            remoteSessionId: $data['remoteSessionId'] ?? null,
            metadata: isset($data['metadata']) ? (array) $data['metadata'] : null,
            progress: isset($data['progress'])
                ? array_map(
                    fn (array $progress) => SessionsOpenProgress::fromArray($progress),
                    $data['progress'],
                )
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status instanceof SessionsOpenStatus ? $this->status->value : $this->status,
            'sessionId' => $this->sessionId,
            'startupPrompts' => $this->startupPrompts,
            'remoteSessionId' => $this->remoteSessionId,
            'metadata' => $this->metadata,
            'progress' => $this->progress === null
                ? null
                : array_map(
                    fn (SessionsOpenProgress|array $progress) => $progress instanceof SessionsOpenProgress
                        ? $progress->toArray()
                        : $progress,
                    $this->progress,
                ),
        ], fn ($value) => $value !== null);
    }
}
