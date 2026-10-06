<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Stable adapter and provider coordinates used by discovery follow-up calls. */
readonly class ModelProviderInstanceReference implements Arrayable
{
    public function __construct(
        public string $adapterId,
        public string $id,
        public string $managementEndpoint,
        public string $providerKind,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            adapterId: $data['adapterId'] ?? '',
            id: $data['id'] ?? '',
            managementEndpoint: $data['managementEndpoint'] ?? '',
            providerKind: $data['providerKind'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'adapterId' => $this->adapterId,
            'id' => $this->id,
            'managementEndpoint' => $this->managementEndpoint,
            'providerKind' => $this->providerKind,
        ];
    }
}
