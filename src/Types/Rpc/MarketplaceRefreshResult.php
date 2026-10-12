<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of refreshing one or more marketplace catalogs.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class MarketplaceRefreshResult implements Arrayable
{
    /**
     * @param  array<MarketplaceRefreshEntry>  $results
     */
    public function __construct(
        public array $results,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            results: array_map(
                fn (array $result) => MarketplaceRefreshEntry::fromArray($result),
                $data['results'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'results' => array_map(
                fn (MarketplaceRefreshEntry $result) => $result->toArray(),
                $this->results,
            ),
        ];
    }
}
