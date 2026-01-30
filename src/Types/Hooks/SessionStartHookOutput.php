<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for session-start hook.
 */
readonly class SessionStartHookOutput implements Arrayable
{
    public function __construct(
        /**
         * Additional context to provide to the agent.
         */
        public ?string $additionalContext = null,
        /**
         * Modified configuration.
         */
        public ?array $modifiedConfig = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            additionalContext: $data['additionalContext'] ?? null,
            modifiedConfig: $data['modifiedConfig'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'additionalContext' => $this->additionalContext,
            'modifiedConfig' => $this->modifiedConfig,
        ], fn ($value) => $value !== null);
    }
}
