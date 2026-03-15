<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Information about the foreground session in TUI+server mode.
 *
 * @implements Arrayable<string, mixed>
 */
readonly class ForegroundSessionInfo implements Arrayable
{
    /**
     * @param  ?string  $sessionId  ID of the foreground session, or null if none
     * @param  ?string  $workspacePath  Workspace path of the foreground session
     */
    public function __construct(
        public ?string $sessionId = null,
        public ?string $workspacePath = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['sessionId'] ?? null,
            workspacePath: $data['workspacePath'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'workspacePath' => $this->workspacePath,
        ], fn ($value) => $value !== null);
    }
}
