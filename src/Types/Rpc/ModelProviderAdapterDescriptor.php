<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ModelProviderAdapterDescriptor implements Arrayable
{
    /**
     * @param  ModelProviderAdapterOperationDescriptor[]  $operations
     */
    public function __construct(
        public string $adapterId,
        public string $displayName,
        public string $providerKind,
        public ModelProviderAutomaticDiscoveryPolicy|array $automaticDiscovery,
        public array $operations = [],
        public ModelProviderAttribution|array|null $provenance = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            adapterId: $data['adapterId'] ?? '',
            displayName: $data['displayName'] ?? '',
            providerKind: $data['providerKind'] ?? '',
            automaticDiscovery: isset($data['automaticDiscovery'])
                ? ModelProviderAutomaticDiscoveryPolicy::fromArray($data['automaticDiscovery'])
                : [],
            operations: array_map(
                fn (array $operation) => ModelProviderAdapterOperationDescriptor::fromArray($operation),
                $data['operations'] ?? [],
            ),
            provenance: isset($data['provenance'])
                ? ModelProviderAttribution::fromArray($data['provenance'])
                : null,
        );
    }

    public function toArray(): array
    {
        $automaticDiscovery = $this->automaticDiscovery instanceof ModelProviderAutomaticDiscoveryPolicy
            ? $this->automaticDiscovery->toArray()
            : $this->automaticDiscovery;
        $provenance = $this->provenance instanceof ModelProviderAttribution
            ? $this->provenance->toArray()
            : $this->provenance;

        return array_filter([
            'adapterId' => $this->adapterId,
            'displayName' => $this->displayName,
            'providerKind' => $this->providerKind,
            'automaticDiscovery' => $automaticDiscovery,
            'operations' => array_map(
                fn (ModelProviderAdapterOperationDescriptor $operation) => $operation->toArray(),
                $this->operations,
            ),
            'provenance' => $provenance,
        ], static fn ($value) => $value !== null);
    }
}
