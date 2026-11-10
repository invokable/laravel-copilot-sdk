<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Runtime catalog entry, including installation and inference metadata.
 *
 * @experimental
 */
readonly class LocalModelDescriptor implements Arrayable
{
    public function __construct(
        public string $id,
        public string $displayName,
        public LocalModelAzureMlIdentity $azureMl,
        public LocalModelSource $source,
        public string $fileName,
        public string $provider,
        public string $license,
        public string $sourceLabel,
        public string $runtime,
        public string $providerName,
        public mixed $serveOptions,
        public LocalModelLimits $limits,
        public bool $supportsToolCalls,
        public mixed $metadata,
        public ?int $approximateBytes = null,
        public ?int $peakDiskBytes = null,
        public ?int $approximateVramBytes = null,
        public ?bool $runtimeProvisioned = null,
        public ?string $selectionId = null,
        public ?string $lifecycle = null,
        public ?LocalModelAutoRegistration $autoLocal = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $azureMl = $data['azureMl'] ?? [];
        $source = $data['source'] ?? [];
        $limits = $data['limits'] ?? [];
        $autoLocal = $data['autoLocal'] ?? null;

        return new static(
            id: $data['id'] ?? '',
            displayName: $data['displayName'] ?? '',
            azureMl: $azureMl instanceof LocalModelAzureMlIdentity
                ? $azureMl
                : LocalModelAzureMlIdentity::fromArray(is_array($azureMl) ? $azureMl : []),
            source: $source instanceof LocalModelSource
                ? $source
                : LocalModelSource::fromArray(is_array($source) ? $source : []),
            fileName: $data['fileName'] ?? '',
            provider: $data['provider'] ?? '',
            license: $data['license'] ?? '',
            sourceLabel: $data['sourceLabel'] ?? '',
            approximateBytes: isset($data['approximateBytes']) ? (int) $data['approximateBytes'] : null,
            peakDiskBytes: isset($data['peakDiskBytes']) ? (int) $data['peakDiskBytes'] : null,
            approximateVramBytes: isset($data['approximateVramBytes']) ? (int) $data['approximateVramBytes'] : null,
            runtime: $data['runtime'] ?? '',
            runtimeProvisioned: $data['runtimeProvisioned'] ?? null,
            providerName: $data['providerName'] ?? '',
            selectionId: $data['selectionId'] ?? null,
            lifecycle: $data['lifecycle'] ?? null,
            serveOptions: $data['serveOptions'] ?? null,
            limits: $limits instanceof LocalModelLimits
                ? $limits
                : LocalModelLimits::fromArray(is_array($limits) ? $limits : []),
            supportsToolCalls: $data['supportsToolCalls'] ?? false,
            autoLocal: $autoLocal instanceof LocalModelAutoRegistration
                ? $autoLocal
                : (is_array($autoLocal) ? LocalModelAutoRegistration::fromArray($autoLocal) : null),
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'displayName' => $this->displayName,
            'azureMl' => $this->azureMl->toArray(),
            'source' => $this->source->toArray(),
            'fileName' => $this->fileName,
            'provider' => $this->provider,
            'license' => $this->license,
            'sourceLabel' => $this->sourceLabel,
            ...array_filter([
                'approximateBytes' => $this->approximateBytes,
                'peakDiskBytes' => $this->peakDiskBytes,
                'approximateVramBytes' => $this->approximateVramBytes,
            ], static fn ($value) => $value !== null),
            'runtime' => $this->runtime,
            ...array_filter([
                'runtimeProvisioned' => $this->runtimeProvisioned,
            ], static fn ($value) => $value !== null),
            'providerName' => $this->providerName,
            ...array_filter([
                'selectionId' => $this->selectionId,
                'lifecycle' => $this->lifecycle,
            ], static fn ($value) => $value !== null),
            'serveOptions' => $this->serveOptions,
            'limits' => $this->limits->toArray(),
            'supportsToolCalls' => $this->supportsToolCalls,
            ...array_filter([
                'autoLocal' => $this->autoLocal?->toArray(),
            ], static fn ($value) => $value !== null),
            'metadata' => $this->metadata,
        ];
    }
}
