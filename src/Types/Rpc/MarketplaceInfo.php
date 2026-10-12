<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Registered marketplace summary.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class MarketplaceInfo implements Arrayable
{
    public function __construct(
        public string $name,
        public string $source,
        public ?bool $available = null,
        public ?bool $isDefault = null,
        public ?bool $managed = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            source: Arr::string($data, 'source'),
            available: isset($data['available']) ? Arr::boolean($data, 'available') : null,
            isDefault: isset($data['isDefault']) ? Arr::boolean($data, 'isDefault') : null,
            managed: isset($data['managed']) ? Arr::boolean($data, 'managed') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'source' => $this->source,
            'available' => $this->available,
            'isDefault' => $this->isDefault,
            'managed' => $this->managed,
        ], fn ($value) => $value !== null);
    }
}
