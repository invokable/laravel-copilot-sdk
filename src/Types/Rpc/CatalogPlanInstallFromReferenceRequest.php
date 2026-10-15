<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Plans installing the resource pinned by a verified materialisation reference.
 *
 * @experimental
 */
readonly class CatalogPlanInstallFromReferenceRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public string $policySessionId,
        public CatalogMaterializationReference $reference,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            policySessionId: $data['policySessionId'],
            reference: CatalogMaterializationReference::fromArray($data['reference']),
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'policySessionId' => $this->policySessionId,
            'reference' => $this->reference->toArray(),
        ];
    }
}
