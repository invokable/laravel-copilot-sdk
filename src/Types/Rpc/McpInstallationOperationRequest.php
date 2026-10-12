<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Inspects or cancels a known MCP installation operation.
 *
 * @experimental
 */
readonly class McpInstallationOperationRequest implements Arrayable
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
