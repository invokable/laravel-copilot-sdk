<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Plans removal of an owned skill installation.
 *
 * @experimental
 */
readonly class SkillPlanUninstallRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public string $installationId,
        public string $policySessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            installationId: $data['installationId'],
            policySessionId: $data['policySessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'installationId' => $this->installationId,
            'policySessionId' => $this->policySessionId,
        ];
    }
}
