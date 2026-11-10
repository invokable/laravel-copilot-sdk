<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Token usage reported by a decision backend.
 *
 * @experimental
 */
readonly class DecisionUsage implements Arrayable
{
    public function __construct(
        public int $inputTokens,
        public int $outputTokens,
        public ?int $cachedInputTokens = null,
        public ?int $cacheWriteTokens = null,
        public ?int $reasoningTokens = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inputTokens: $data['inputTokens'],
            outputTokens: $data['outputTokens'],
            cachedInputTokens: $data['cachedInputTokens'] ?? null,
            cacheWriteTokens: $data['cacheWriteTokens'] ?? null,
            reasoningTokens: $data['reasoningTokens'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'inputTokens' => $this->inputTokens,
            'cachedInputTokens' => $this->cachedInputTokens,
            'cacheWriteTokens' => $this->cacheWriteTokens,
            'outputTokens' => $this->outputTokens,
            'reasoningTokens' => $this->reasoningTokens,
        ], fn ($value) => $value !== null);
    }
}
