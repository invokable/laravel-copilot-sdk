<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Lists or recovers owned MCP installations.
 *
 * @experimental
 */
readonly class McpInstallationsRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public string $policySessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            policySessionId: $data['policySessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'policySessionId' => $this->policySessionId,
        ];
    }
}
