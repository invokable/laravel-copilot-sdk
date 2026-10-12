<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Applies a previously prepared MCP installation.
 *
 * @experimental
 */
readonly class McpApplyInstallRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public string $operationId,
        public string $policySessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            operationId: $data['operationId'],
            policySessionId: $data['policySessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'operationId' => $this->operationId,
            'policySessionId' => $this->policySessionId,
        ];
    }
}
