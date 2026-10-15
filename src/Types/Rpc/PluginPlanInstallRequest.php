<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Side-effect-free planning of an Agent Plugin search candidate.
 *
 * @experimental
 */
readonly class PluginPlanInstallRequest implements Arrayable
{
    public function __construct(
        public string $candidateHandle,
        public CatalogClientContract $contract,
        public string $policySessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            candidateHandle: $data['candidateHandle'],
            contract: CatalogClientContract::fromArray($data['contract']),
            policySessionId: $data['policySessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'candidateHandle' => $this->candidateHandle,
            'contract' => $this->contract->toArray(),
            'policySessionId' => $this->policySessionId,
        ];
    }
}
