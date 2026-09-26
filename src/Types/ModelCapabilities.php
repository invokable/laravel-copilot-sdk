<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AdaptiveThinkingSupport;

/**
 * Model capabilities and limits.
 */
readonly class ModelCapabilities implements Arrayable
{
    /**
     * @param  array  $supports  Supports configuration
     * @param  array  $limits  Limits configuration
     */
    public function __construct(
        public array $supports,
        public array $limits,
    ) {}

    /**
     * Create from array.
     *
     * @param  array{supports: array{vision: bool}, limits: array{max_prompt_tokens?: int, max_context_window_tokens: int, vision?: array{supported_media_types: array<string>, max_prompt_images: int, max_prompt_image_size: int}}}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            supports: Arr::array($data, 'supports', []),
            limits: Arr::array($data, 'limits', []),
        );
    }

    /**
     * Check if vision is supported.
     */
    public function supportsVision(): bool
    {
        return $this->supports['vision'] ?? false;
    }

    /**
     * Check if reasoning effort is supported.
     */
    public function supportsReasoningEffort(): bool
    {
        return $this->supports['reasoningEffort'] ?? false;
    }

    /**
     * Check if structured outputs are supported.
     */
    public function supportsStructuredOutputs(): bool
    {
        return $this->supports['structured_outputs'] ?? false;
    }

    /**
     * Get the model's adaptive-thinking capability, when reported.
     */
    public function adaptiveThinkingSupport(): ?AdaptiveThinkingSupport
    {
        $support = $this->supports['adaptive_thinking'] ?? null;

        return is_string($support) ? AdaptiveThinkingSupport::tryFrom($support) : null;
    }

    /**
     * Get max context window tokens.
     */
    public function maxContextWindowTokens(): int
    {
        return $this->limits['max_context_window_tokens'] ?? 0;
    }

    /**
     * Get max output tokens, if defined.
     */
    public function maxOutputTokens(): ?int
    {
        return $this->limits['max_output_tokens'] ?? null;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'supports' => $this->supports,
            'limits' => $this->limits,
        ];
    }
}
