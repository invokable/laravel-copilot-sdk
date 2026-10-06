<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Provider-native model metadata, including adapter-specific details. */
readonly class DiscoveredModel implements Arrayable
{
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?string $digest = null,
        public ?string $modifiedAt = null,
        public ?int $sizeBytes = null,
        public ?array $capabilities = null,
        public ModelArtifactDetails|array|null $details = null,
        public ModelProviderProvenance|array|null $provenance = null,
        public array $warnings = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? null,
            digest: $data['digest'] ?? null,
            modifiedAt: $data['modifiedAt'] ?? null,
            sizeBytes: isset($data['sizeBytes']) ? (int) $data['sizeBytes'] : null,
            capabilities: $data['capabilities'] ?? null,
            details: isset($data['details']) ? ModelArtifactDetails::fromArray($data['details']) : null,
            provenance: isset($data['provenance'])
                ? ModelProviderProvenance::fromArray($data['provenance'])
                : null,
            warnings: array_map(
                fn (array $warning) => ModelProviderWarning::fromArray($warning),
                $data['warnings'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        $details = $this->details instanceof ModelArtifactDetails ? $this->details->toArray() : $this->details;
        $provenance = $this->provenance instanceof ModelProviderProvenance ? $this->provenance->toArray() : $this->provenance;

        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'digest' => $this->digest,
            'modifiedAt' => $this->modifiedAt,
            'sizeBytes' => $this->sizeBytes,
            'capabilities' => $this->capabilities,
            'details' => $details,
            'provenance' => $provenance,
            'warnings' => $this->warnings !== []
                ? array_map(fn (ModelProviderWarning $warning) => $warning->toArray(), $this->warnings)
                : null,
        ], static fn ($value) => $value !== null);
    }
}
