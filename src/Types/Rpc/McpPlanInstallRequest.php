<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanScope;

/**
 * A side-effect-free request for an MCP install plan.
 *
 * @experimental
 */
readonly class McpPlanInstallRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public McpPlanInstallSourceCandidate|McpPlanInstallSourceCard $source,
        public ?McpPlanScope $scope = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $sourceData = $data['source'];
        $source = ($sourceData['kind'] ?? '') === 'candidate'
            ? McpPlanInstallSourceCandidate::fromArray($sourceData)
            : McpPlanInstallSourceCard::fromArray($sourceData);

        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            source: $source,
            scope: isset($data['scope']) ? McpPlanScope::from($data['scope']) : null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'contract' => $this->contract->toArray(),
            'source' => $this->source->toArray(),
        ];
        if ($this->scope !== null) {
            $arr['scope'] = $this->scope->value;
        }

        return $arr;
    }
}
