<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ModelProviderProvenance implements Arrayable
{
    public function __construct(
        public string $adapterId,
        public string $providerKind,
        public string $source,
        public ?string $ownerId = null,
        public ?string $ownerDisplayName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            adapterId: $data['adapterId'] ?? '',
            providerKind: $data['providerKind'] ?? '',
            source: $data['source'] ?? '',
            ownerId: $data['ownerId'] ?? null,
            ownerDisplayName: $data['ownerDisplayName'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'adapterId' => $this->adapterId,
            'providerKind' => $this->providerKind,
            'source' => $this->source,
            'ownerId' => $this->ownerId,
            'ownerDisplayName' => $this->ownerDisplayName,
        ], static fn ($value) => $value !== null);
    }
}
