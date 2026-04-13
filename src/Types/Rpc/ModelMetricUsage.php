<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

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
     */
    public function __construct(
        public int $inputTokens,
        public int $outputTokens,
        public int $cacheReadTokens,
        public int $cacheWriteTokens,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inputTokens: $data['inputTokens'],
            outputTokens: $data['outputTokens'],
            cacheReadTokens: $data['cacheReadTokens'],
            cacheWriteTokens: $data['cacheWriteTokens'],
        );
    }

    public function toArray(): array
    {
        return [
            'inputTokens' => $this->inputTokens,
            'outputTokens' => $this->outputTokens,
            'cacheReadTokens' => $this->cacheReadTokens,
            'cacheWriteTokens' => $this->cacheWriteTokens,
        ];
    }
}
