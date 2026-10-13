<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Changes whether an owned skill installation is enabled.
 *
 * @experimental
 */
readonly class SkillSetEnabledRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public string $installationId,
        public bool $enabled,
        public string $policySessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            installationId: $data['installationId'],
            enabled: $data['enabled'],
            policySessionId: $data['policySessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'installationId' => $this->installationId,
            'enabled' => $this->enabled,
            'policySessionId' => $this->policySessionId,
        ];
    }
}
