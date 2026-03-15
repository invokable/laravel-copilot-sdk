<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for session-end hook.
 */
readonly class SessionEndHookOutput implements Arrayable
{
    /**
     * @param  ?bool  $suppressOutput  Whether to suppress output
     * @param  ?array  $cleanupActions  Cleanup actions to perform
     * @param  ?string  $sessionSummary  Session summary
     */
    public function __construct(
        public ?bool $suppressOutput = null,
        public ?array $cleanupActions = null,
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
