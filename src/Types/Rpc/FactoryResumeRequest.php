<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for resuming a halted factory run.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryResumeRequest implements Arrayable
{
    /**
     * @param  string  $runId  Factory run identifier to resume.
     * @param  FactoryRunLimits|array|null  $limits  Optional per-invocation resource ceiling overrides.
     */
    public function __construct(
        public string $runId,
        public FactoryRunLimits|array|null $limits = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $limits = $data['limits'] ?? null;

        return new self(
            runId: Arr::string($data, 'runId'),
            limits: $limits !== null
                ? ($limits instanceof FactoryRunLimits ? $limits : FactoryRunLimits::fromArray($limits))
                : null,
        );
    }

    public function toArray(): array
    {
        $limits = $this->limits instanceof FactoryRunLimits ? $this->limits->toArray() : $this->limits;

        return array_filter([
            'runId' => $this->runId,
            'limits' => $limits,
        ], fn ($v) => $v !== null);
    }
}
