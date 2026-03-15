<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for user-prompt-submitted hook.
 */
readonly class UserPromptSubmittedHookOutput implements Arrayable
{
    /**
     * @param  ?string  $modifiedPrompt  Modified prompt to use
     * @param  ?string  $additionalContext  Additional context to provide to the agent
     * @param  ?bool  $suppressOutput  Whether to suppress output
     */
    public function __construct(
        public ?string $modifiedPrompt = null,
        public ?string $additionalContext = null,
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
