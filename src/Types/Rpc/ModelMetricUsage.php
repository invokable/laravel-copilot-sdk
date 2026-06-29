<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Token usage metrics for a model.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelMetricUsage implements Arrayable
{
    /**
     * @param  int  $inputTokens  Total input tokens consumed
     * @param  int  $outputTokens  Total output tokens produced
     * @param  int  $cacheReadTokens  Total tokens read from prompt cache
     * @param  int  $cacheWriteTokens  Total tokens written to prompt cache
     * @param  ?int  $reasoningTokens  Total output tokens used for reasoning (e.g., chain-of-thought)
     */
    public function __construct(
        public int $inputTokens,
        public int $outputTokens,
        public int $cacheReadTokens,
        public int $cacheWriteTokens,
        public ?int $reasoningTokens = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inputTokens: Arr::integer($data, 'inputTokens'),
            outputTokens: Arr::integer($data, 'outputTokens'),
            cacheReadTokens: Arr::integer($data, 'cacheReadTokens'),
            cacheWriteTokens: Arr::integer($data, 'cacheWriteTokens'),
            reasoningTokens: $data['reasoningTokens'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'inputTokens' => $this->inputTokens,
            'outputTokens' => $this->outputTokens,
            'cacheReadTokens' => $this->cacheReadTokens,
            'cacheWriteTokens' => $this->cacheWriteTokens,
            'reasoningTokens' => $this->reasoningTokens,
        ], fn ($v) => $v !== null);
    }
}
