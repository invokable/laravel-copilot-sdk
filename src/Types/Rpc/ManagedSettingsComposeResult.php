<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Effective managed settings preview for supplied policy documents. */
readonly class ManagedSettingsComposeResult implements Arrayable
{
    /** @param array $resolved Effective runtime settings. @param array<ManagedSettingsLayer|array> $layers Canonical input layers. @param array<ManagedSettingsDiagnostic|array> $diagnostics Validation findings. */
    public function __construct(
        public array $resolved = [],
        public ManagedSettingsValues|array|null $values = null,
        public ManagedSettingsMeta|array|null $meta = null,
        public array $layers = [],
        public array $diagnostics = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            resolved: $data['resolved'] ?? [],
            values: isset($data['values']) ? ($data['values'] instanceof ManagedSettingsValues ? $data['values'] : ManagedSettingsValues::fromArray($data['values'])) : null,
            meta: isset($data['meta']) ? ($data['meta'] instanceof ManagedSettingsMeta ? $data['meta'] : ManagedSettingsMeta::fromArray($data['meta'])) : null,
            layers: array_map(static fn ($layer) => $layer instanceof ManagedSettingsLayer ? $layer : ManagedSettingsLayer::fromArray($layer), $data['layers'] ?? []),
            diagnostics: array_map(static fn ($diagnostic) => $diagnostic instanceof ManagedSettingsDiagnostic ? $diagnostic : ManagedSettingsDiagnostic::fromArray($diagnostic), $data['diagnostics'] ?? []),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'resolved' => $this->resolved,
            'values' => $this->values instanceof ManagedSettingsValues ? $this->values->toArray() : $this->values,
            'meta' => $this->meta instanceof ManagedSettingsMeta ? $this->meta->toArray() : $this->meta,
            'layers' => array_map(static fn ($layer) => $layer instanceof ManagedSettingsLayer ? $layer->toArray() : $layer, $this->layers),
            'diagnostics' => array_map(static fn ($diagnostic) => $diagnostic instanceof ManagedSettingsDiagnostic ? $diagnostic->toArray() : $diagnostic, $this->diagnostics),
        ], fn ($value) => $value !== null);
    }
}
