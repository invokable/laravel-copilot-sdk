<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for user-prompt-submitted hook.
 */
readonly class UserPromptSubmittedHookOutput implements Arrayable
{
    public function __construct(
        /**
         * Modified prompt to use.
         */
        public ?string $modifiedPrompt = null,
        /**
         * Additional context to provide to the agent.
         */
        public ?string $additionalContext = null,
        /**
         * Whether to suppress output.
         */
        public ?bool $suppressOutput = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            modifiedPrompt: $data['modifiedPrompt'] ?? null,
            additionalContext: $data['additionalContext'] ?? null,
            suppressOutput: $data['suppressOutput'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'modifiedPrompt' => $this->modifiedPrompt,
            'additionalContext' => $this->additionalContext,
            'suppressOutput' => $this->suppressOutput,
        ], fn ($value) => $value !== null);
    }
}
