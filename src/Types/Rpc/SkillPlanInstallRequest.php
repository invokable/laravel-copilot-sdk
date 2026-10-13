<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Plans installation of a selected AI skill candidate.
 *
 * @experimental
 */
readonly class SkillPlanInstallRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public string $candidateHandle,
        public string $policySessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            candidateHandle: $data['candidateHandle'],
            policySessionId: $data['policySessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'candidateHandle' => $this->candidateHandle,
            'policySessionId' => $this->policySessionId,
        ];
    }
}
