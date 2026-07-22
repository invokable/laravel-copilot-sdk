<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Options controlling factory invocation.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RunOptions implements Arrayable
{
    /**
     * @param  FactoryRunLimits|array|null  $limits  Per-invocation resource ceiling overrides.
     * @param  ?string  $resumeFromRunId  Run identifier whose journal and progress should seed this resumed run.
     */
    public function __construct(
        public FactoryRunLimits|array|null $limits = null,
        public ?string $resumeFromRunId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $limits = $data['limits'] ?? null;

        return new self(
            limits: $limits !== null
                ? ($limits instanceof FactoryRunLimits ? $limits : FactoryRunLimits::fromArray($limits))
                : null,
            resumeFromRunId: $data['resumeFromRunId'] ?? null,
        );
    }

    public function toArray(): array
    {
        $limits = $this->limits instanceof FactoryRunLimits ? $this->limits->toArray() : $this->limits;

        return array_filter([
            'limits' => $limits,
            'resumeFromRunId' => $this->resumeFromRunId,
        ], fn ($v) => $v !== null);
    }
}
