<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FactoryRunFailureKind;
use Revolution\Copilot\Enums\FactoryRunFailureType;

/**
 * Machine-readable factory run failure.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryRunFailure implements Arrayable
{
    /**
     * @param  string  $runId  Factory run identifier.
     * @param  FactoryRunFailureType|string  $type  Discriminator for the failure.
     * @param  FactoryRunFailureKind|string|null  $kind  Resource ceiling that stopped the run.
     * @param  ?float  $value  Approved effective ceiling that was reached.
     * @param  ?string  $reason  Human-readable reason the resume did not proceed.
     */
    public function __construct(
        public string $runId,
        public FactoryRunFailureType|string $type,
        public FactoryRunFailureKind|string|null $kind = null,
        public ?float $value = null,
        public ?string $reason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            runId: Arr::string($data, 'runId'),
            type: $data['type'] instanceof FactoryRunFailureType ? $data['type'] : FactoryRunFailureType::from($data['type']),
            kind: isset($data['kind'])
                ? ($data['kind'] instanceof FactoryRunFailureKind ? $data['kind'] : FactoryRunFailureKind::from($data['kind']))
                : null,
            value: $data['value'] ?? null,
            reason: $data['reason'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'runId' => $this->runId,
            'type' => $this->type instanceof FactoryRunFailureType ? $this->type->value : $this->type,
            'kind' => $this->kind instanceof FactoryRunFailureKind ? $this->kind->value : $this->kind,
            'value' => $this->value,
            'reason' => $this->reason,
        ], fn ($v) => $v !== null);
    }
}
