<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** A discovered provider instance and its inference routing metadata. */
readonly class ModelProviderInstance implements Arrayable
{
    public function __construct(
        public string $displayName,
        public ModelProviderInstanceReference|array $reference,
        public ModelProviderProvenance|array|null $provenance = null,
        public ?string $inferenceEndpoint = null,
        public ?string $inferenceTransport = null,
        public ?string $inferenceType = null,
        public ?string $inferenceWireApi = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            displayName: $data['displayName'] ?? '',
            reference: isset($data['reference'])
                ? ModelProviderInstanceReference::fromArray($data['reference'])
                : [],
            provenance: isset($data['provenance'])
                ? ModelProviderProvenance::fromArray($data['provenance'])
                : null,
            inferenceEndpoint: $data['inferenceEndpoint'] ?? null,
            inferenceTransport: $data['inferenceTransport'] ?? null,
            inferenceType: $data['inferenceType'] ?? null,
            inferenceWireApi: $data['inferenceWireApi'] ?? null,
        );
    }

    public function toArray(): array
    {
        $reference = $this->reference instanceof ModelProviderInstanceReference
            ? $this->reference->toArray()
            : $this->reference;
        $provenance = $this->provenance instanceof ModelProviderProvenance
            ? $this->provenance->toArray()
            : $this->provenance;

        return array_filter([
            'displayName' => $this->displayName,
            'reference' => $reference,
            'provenance' => $provenance,
            'inferenceEndpoint' => $this->inferenceEndpoint,
            'inferenceTransport' => $this->inferenceTransport,
            'inferenceType' => $this->inferenceType,
            'inferenceWireApi' => $this->inferenceWireApi,
        ], static fn ($value) => $value !== null);
    }
}
