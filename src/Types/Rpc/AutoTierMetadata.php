<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Account-bound routing metadata for the virtual Auto model.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AutoTierMetadata implements Arrayable
{
    /**
     * @param  array<AutoTierDescriptor>  $tiers  Routing preferences in server presentation order.
     */
    public function __construct(
        public string $defaultTier,
        public array $tiers,
        public ?string $providerId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            defaultTier: Arr::string($data, 'defaultTier'),
            tiers: array_map(
                fn (array $tier) => AutoTierDescriptor::fromArray($tier),
                Arr::array($data, 'tiers', []),
            ),
            providerId: $data['providerId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'defaultTier' => $this->defaultTier,
            'tiers' => array_map(fn (AutoTierDescriptor $tier) => $tier->toArray(), $this->tiers),
            'providerId' => $this->providerId,
        ], fn ($value) => $value !== null);
    }
}
