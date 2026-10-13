<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Applies a previously generated skill installation plan.
 *
 * @experimental
 */
readonly class SkillApplyInstallRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public string $planHandle,
        public string $policySessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            planHandle: $data['planHandle'],
            policySessionId: $data['policySessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'planHandle' => $this->planHandle,
            'policySessionId' => $this->policySessionId,
        ];
    }
}
