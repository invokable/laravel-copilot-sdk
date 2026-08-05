<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for user-prompt-transformed hook.
 */
readonly class UserPromptTransformedHookOutput implements Arrayable
{
    /**
     * @param  ?string  $modifiedTransformedPrompt  Replacement transformed prompt
     */
    public function __construct(
        public ?string $modifiedTransformedPrompt = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            modifiedTransformedPrompt: $data['modifiedTransformedPrompt'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'modifiedTransformedPrompt' => $this->modifiedTransformedPrompt,
        ], fn ($value): bool => $value !== null);
    }
}
