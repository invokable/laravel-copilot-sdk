<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\MarketplaceMigrationStatus;

/**
 * @experimental
 */
readonly class MarketplaceMigrationResult implements Arrayable
{
    public function __construct(
        public MarketplaceMigrationStatus $status,
        public ?string $marketplace = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: MarketplaceMigrationStatus::from(Arr::string($data, 'status')),
            marketplace: $data['marketplace'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status->value,
            'marketplace' => $this->marketplace,
        ], fn ($value) => $value !== null);
    }
}
