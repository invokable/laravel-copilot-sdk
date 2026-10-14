<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Plans a Skill installation from a verified materialisation reference.
 *
 * @experimental
 */
readonly class SkillPlanInstallFromReferenceRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public CatalogMaterializationReference $reference,
        public string $policySessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            reference: CatalogMaterializationReference::fromArray($data['reference']),
            policySessionId: $data['policySessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'reference' => $this->reference->toArray(),
            'policySessionId' => $this->policySessionId,
        ];
    }
}
