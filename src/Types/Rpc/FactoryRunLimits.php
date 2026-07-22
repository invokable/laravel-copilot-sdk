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
     * @param  ?float  $timeout  Factory active-run timeout in milliseconds.
     */
    public function __construct(
        public ?int $maxConcurrentSubagents = null,
        public ?int $maxTotalSubagents = null,
        public ?float $timeout = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            maxConcurrentSubagents: $data['maxConcurrentSubagents'] ?? null,
            maxTotalSubagents: $data['maxTotalSubagents'] ?? null,
            timeout: $data['timeout'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'maxConcurrentSubagents' => $this->maxConcurrentSubagents,
            'maxTotalSubagents' => $this->maxTotalSubagents,
            'timeout' => $this->timeout,
        ], fn ($v) => $v !== null);
    }
}
