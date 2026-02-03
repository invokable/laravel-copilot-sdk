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
    public function __construct(
        /**
         * ID of the foreground session, or null if none.
         */
        public ?string $sessionId = null,

        /**
         * Workspace path of the foreground session.
         */
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
