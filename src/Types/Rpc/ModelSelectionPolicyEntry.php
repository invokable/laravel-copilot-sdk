<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Selection policy for a catalog or registered model.
 *
 * @experimental
 */
readonly class ModelSelectionPolicyEntry implements Arrayable
{
    /**
     * @param  array<int, string>  $reasoningEfforts
     * @param  array<int, ModelContextTierOption>|null  $contextTiers
     */
    public function __construct(
        public string $modelId,
        public float $multiplier,
        public bool $needsEnablement,
        public array $reasoningEfforts,
        public ?string $providerId = null,
        public ?string $pickerCategory = null,
        public ?string $priceCategory = null,
        public ?string $defaultReasoningEffort = null,
        public ?array $contextTiers = null,
        public ?string $defaultContextTier = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: Arr::string($data, 'modelId'),
            multiplier: $data['multiplier'] ?? 0,
            needsEnablement: $data['needsEnablement'] ?? false,
            reasoningEfforts: Arr::array($data, 'reasoningEfforts', []),
            providerId: $data['providerId'] ?? null,
            pickerCategory: $data['pickerCategory'] ?? null,
            priceCategory: $data['priceCategory'] ?? null,
            defaultReasoningEffort: $data['defaultReasoningEffort'] ?? null,
            contextTiers: isset($data['contextTiers'])
                ? array_map(fn (array $tier) => ModelContextTierOption::fromArray($tier), $data['contextTiers'])
                : null,
            defaultContextTier: $data['defaultContextTier'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'modelId' => $this->modelId,
            'providerId' => $this->providerId,
            'multiplier' => $this->multiplier,
            'needsEnablement' => $this->needsEnablement,
            'pickerCategory' => $this->pickerCategory,
            'priceCategory' => $this->priceCategory,
            'reasoningEfforts' => $this->reasoningEfforts,
            'defaultReasoningEffort' => $this->defaultReasoningEffort,
            'contextTiers' => $this->contextTiers !== null
                ? array_map(fn (ModelContextTierOption $tier) => $tier->toArray(), $this->contextTiers)
                : null,
            'defaultContextTier' => $this->defaultContextTier,
        ], fn ($value) => $value !== null);
    }
}
