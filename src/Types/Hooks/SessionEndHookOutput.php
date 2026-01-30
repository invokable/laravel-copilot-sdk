<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for session-end hook.
 */
readonly class SessionEndHookOutput implements Arrayable
{
    public function __construct(
        /**
         * Whether to suppress output.
         */
        public ?bool $suppressOutput = null,
        /**
         * Cleanup actions to perform.
         */
        public ?array $cleanupActions = null,
        /**
         * Session summary.
         */
        public ?string $sessionSummary = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            suppressOutput: $data['suppressOutput'] ?? null,
            cleanupActions: $data['cleanupActions'] ?? null,
            sessionSummary: $data['sessionSummary'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'suppressOutput' => $this->suppressOutput,
            'cleanupActions' => $this->cleanupActions,
            'sessionSummary' => $this->sessionSummary,
        ], fn ($value) => $value !== null);
    }
}
