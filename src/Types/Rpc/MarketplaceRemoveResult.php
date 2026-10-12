<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Outcome of a marketplace removal attempt.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class MarketplaceRemoveResult implements Arrayable
{
    /**
     * @param  ?array<string>  $dependentPlugins
     */
    public function __construct(
        public bool $removed,
        public ?array $dependentPlugins = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            removed: Arr::boolean($data, 'removed'),
            dependentPlugins: $data['dependentPlugins'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'removed' => $this->removed,
            'dependentPlugins' => $this->dependentPlugins,
        ], fn ($value) => $value !== null);
    }
}
