<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Search query for plugins across configured marketplaces.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginMarketplaceSearchRequest implements Arrayable
{
    public function __construct(
        public string $query,
        public ?bool $forceRefresh = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            query: Arr::string($data, 'query'),
            forceRefresh: isset($data['forceRefresh']) ? Arr::boolean($data, 'forceRefresh') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'query' => $this->query,
            'forceRefresh' => $this->forceRefresh,
        ], fn ($value) => $value !== null);
    }
}
