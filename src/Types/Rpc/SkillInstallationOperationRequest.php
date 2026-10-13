<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Inspects or cancels a known skill installation operation.
 *
 * @experimental
 */
readonly class SkillInstallationOperationRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public string $operationId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            operationId: $data['operationId'],
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'operationId' => $this->operationId,
        ];
    }
}
