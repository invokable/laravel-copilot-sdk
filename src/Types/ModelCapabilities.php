<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Model capabilities and limits.
 */
readonly class ModelCapabilities implements Arrayable
{
    public function __construct(
        /** Supports configuration */
        public array $supports,
        /** Limits configuration */
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
            supports: $data['supports'],
            limits: $data['limits'],
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
     * Get max context window tokens.
     */
    public function maxContextWindowTokens(): int
    {
        return $this->limits['max_context_window_tokens'];
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
