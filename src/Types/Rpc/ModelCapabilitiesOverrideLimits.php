<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Token limits for prompts, outputs, and context window (override).
 */
readonly class ModelCapabilitiesOverrideLimits implements Arrayable
{
    /**
     * @param  ?int  $max_prompt_tokens  Maximum number of prompt/input tokens
     * @param  ?int  $max_output_tokens  Maximum number of output/completion tokens
     * @param  ?int  $max_context_window_tokens  Maximum total context window size in tokens
     * @param  ModelCapabilitiesOverrideLimitsVision|array|null  $vision  Vision-specific limit overrides
     */
    public function __construct(
        public ?int $max_prompt_tokens = null,
        public ?int $max_output_tokens = null,
        public ?int $max_context_window_tokens = null,
        public ModelCapabilitiesOverrideLimitsVision|array|null $vision = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $vision = isset($data['vision'])
            ? ($data['vision'] instanceof ModelCapabilitiesOverrideLimitsVision
                ? $data['vision']
                : ModelCapabilitiesOverrideLimitsVision::fromArray($data['vision']))
            : null;

        return new self(
            max_prompt_tokens: $data['max_prompt_tokens'] ?? null,
            max_output_tokens: $data['max_output_tokens'] ?? null,
            max_context_window_tokens: $data['max_context_window_tokens'] ?? null,
            vision: $vision,
        );
    }

    public function toArray(): array
    {
        $vision = $this->vision instanceof ModelCapabilitiesOverrideLimitsVision
            ? $this->vision->toArray()
            : $this->vision;

        return array_filter([
            'max_prompt_tokens' => $this->max_prompt_tokens,
            'max_output_tokens' => $this->max_output_tokens,
            'max_context_window_tokens' => $this->max_context_window_tokens,
            'vision' => $vision,
        ], fn ($v) => $v !== null);
    }
}
