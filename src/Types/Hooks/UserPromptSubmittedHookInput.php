<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for user-prompt-submitted hook.
 */
readonly class UserPromptSubmittedHookInput extends BaseHookInput
{
    public function __construct(
        int $timestamp,
        string $cwd,
        public string $prompt,
    ) {
        parent::__construct($timestamp, $cwd);
    }

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            timestamp: $data['timestamp'] ?? 0,
            cwd: $data['cwd'] ?? '',
            prompt: $data['prompt'] ?? '',
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'prompt' => $this->prompt,
        ];
    }
}
