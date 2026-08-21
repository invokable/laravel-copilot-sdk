<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A computed MCP install plan. Nothing has been applied.
 *
 * @experimental
 */
readonly class McpPlanInstallPlanned implements Arrayable
{
    public string $kind;

    public function __construct(
        public McpInstallPlan $plan,
        public CatalogNegotiatedContract $negotiated,
    ) {
        $this->kind = 'planned';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            plan: McpInstallPlan::fromArray($data['plan']),
            negotiated: CatalogNegotiatedContract::fromArray($data['negotiated']),
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'plan' => $this->plan->toArray(),
            'negotiated' => $this->negotiated->toArray(),
        ];
    }
}
