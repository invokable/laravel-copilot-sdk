<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All registered marketplaces, including built-in defaults.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class MarketplaceListResult implements Arrayable
{
    /**
     * @param  array<MarketplaceInfo>  $marketplaces
     */
    public function __construct(
        public array $marketplaces,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            marketplaces: array_map(
                fn (array $marketplace) => MarketplaceInfo::fromArray($marketplace),
                $data['marketplaces'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'marketplaces' => array_map(
                fn (MarketplaceInfo $marketplace) => $marketplace->toArray(),
                $this->marketplaces,
            ),
        ];
    }
}
