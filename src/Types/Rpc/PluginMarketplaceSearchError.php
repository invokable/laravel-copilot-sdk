<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Marketplace search error.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginMarketplaceSearchError implements Arrayable
{
    public function __construct(
        public string $marketplace,
        public string $error,
        public ?string $scope = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            marketplace: Arr::string($data, 'marketplace'),
            error: Arr::string($data, 'error'),
            scope: isset($data['scope']) ? Arr::string($data, 'scope') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'marketplace' => $this->marketplace,
            'error' => $this->error,
            'scope' => $this->scope,
        ], fn ($value) => $value !== null);
    }
}
