<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Internal parameters for resuming a factory run from a tool.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 *
 * @internal
 */
readonly class FactoryToolResumeRequest implements Arrayable
{
    /**
     * @param  string  $runId  Factory run identifier.
     * @param  FactoryRunLimits|array|null  $limits  Optional per-invocation resource ceiling overrides.
     * @param  ?string  $toolCallId  Opaque identifier of the originating tool call.
     */
    public function __construct(
        public string $runId,
        public FactoryRunLimits|array|null $limits = null,
        public ?string $toolCallId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $limits = $data['limits'] ?? null;

        return new self(
            runId: Arr::string($data, 'runId'),
            limits: $limits !== null
                ? ($limits instanceof FactoryRunLimits ? $limits : FactoryRunLimits::fromArray($limits))
                : null,
            toolCallId: $data['toolCallId'] ?? null,
        );
    }

    public function toArray(): array
    {
        $limits = $this->limits instanceof FactoryRunLimits ? $this->limits->toArray() : $this->limits;

        return array_filter([
            'runId' => $this->runId,
            'limits' => $limits,
            'toolCallId' => $this->toolCallId,
        ], fn ($v) => $v !== null);
    }
}
