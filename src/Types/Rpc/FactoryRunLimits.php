<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Wire-only per-invocation factory resource ceiling overrides.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryRunLimits implements Arrayable
{
    /**
     * @param  ?int  $maxConcurrentSubagents  Maximum number of factory subagents that may run concurrently.
     * @param  ?int  $maxTotalSubagents  Maximum total number of factory subagents that may be admitted.
     * @param  ?float  $timeoutSeconds  Factory active-run timeout, in seconds.
     * @param  ?float  $maxAiCredits  Maximum AI credits the run may consume.
     */
    public function __construct(
        public ?int $maxConcurrentSubagents = null,
        public ?int $maxTotalSubagents = null,
        public ?float $timeoutSeconds = null,
        public ?float $maxAiCredits = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            maxConcurrentSubagents: $data['maxConcurrentSubagents'] ?? null,
            maxTotalSubagents: $data['maxTotalSubagents'] ?? null,
            timeoutSeconds: $data['timeoutSeconds'] ?? null,
            maxAiCredits: $data['maxAiCredits'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'maxConcurrentSubagents' => $this->maxConcurrentSubagents,
            'maxTotalSubagents' => $this->maxTotalSubagents,
            'timeoutSeconds' => $this->timeoutSeconds,
            'maxAiCredits' => $this->maxAiCredits,
        ], fn ($v) => $v !== null);
    }
}
