<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Requests recommendations for a product and complete task.
 *
 * @experimental
 */
readonly class CatalogRecommendRequest implements Arrayable
{
    /**
     * @param  CatalogClientContract  $contract  Protocol version and required capabilities.
     * @param  string  $query  Complete task description.
     * @param  string  $product  Official product name.
     * @param  string|null  $policySessionId  Existing attached session to bind to.
     */
    public function __construct(
        public CatalogClientContract $contract,
        public string $query,
        public string $product,
        public ?string $policySessionId = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            query: $data['query'],
            product: $data['product'],
            policySessionId: $data['policySessionId'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'contract' => $this->contract->toArray(),
        ];

        if ($this->policySessionId !== null) {
            $data['policySessionId'] = $this->policySessionId;
        }

        $data['query'] = $this->query;
        $data['product'] = $this->product;

        return $data;
    }
}
