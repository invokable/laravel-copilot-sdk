<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Support\Arr;

/**
 * Input for user-prompt-submitted hook.
 */
readonly class UserPromptSubmittedHookInput extends BaseHookInput
{
    /**
     * @param  string  $sessionId  The runtime session ID of the session that triggered the hook
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $prompt  The user's submitted prompt
     */
    public function __construct(
        string $sessionId,
        int $timestamp,
        string $cwd,
        public string $prompt,
    ) {
        parent::__construct($sessionId, $timestamp, $cwd);
    }

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            sessionId: $data['sessionId'] ?? '',
            timestamp: $data['timestamp'] ?? 0,
            cwd: $data['cwd'] ?? '',
            prompt: Arr::string($data, 'prompt', ''),
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
