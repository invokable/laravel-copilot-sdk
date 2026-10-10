<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Effective managed settings and contributing policy layers. */
readonly class ManagedSettingsResolveResult implements Arrayable
{
    /** @param array $resolved Runtime-composed effective settings. @param array<ManagedSettingsLayer|array> $layers Policy layers, strongest first. @param array<ManagedSettingsDiagnostic|array> $diagnostics Validation and policy source findings. */
    public function __construct(
        public array $resolved = [],
        public ?string $account = null,
        public ManagedSettingsValues|array|null $values = null,
        public ManagedSettingsMeta|array|null $meta = null,
        public array $layers = [],
        public array $diagnostics = [],
        public ?ManagedPermissionsContext $permissionsContext = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            resolved: $data['resolved'] ?? [],
            account: $data['account'] ?? null,
            values: isset($data['values']) ? ($data['values'] instanceof ManagedSettingsValues ? $data['values'] : ManagedSettingsValues::fromArray($data['values'])) : null,
            meta: isset($data['meta']) ? ($data['meta'] instanceof ManagedSettingsMeta ? $data['meta'] : ManagedSettingsMeta::fromArray($data['meta'])) : null,
            layers: array_map(static fn ($layer) => $layer instanceof ManagedSettingsLayer ? $layer : ManagedSettingsLayer::fromArray($layer), $data['layers'] ?? []),
            diagnostics: array_map(static fn ($diagnostic) => $diagnostic instanceof ManagedSettingsDiagnostic ? $diagnostic : ManagedSettingsDiagnostic::fromArray($diagnostic), $data['diagnostics'] ?? []),
            permissionsContext: isset($data['permissionsContext'])
                ? ($data['permissionsContext'] instanceof ManagedPermissionsContext
                    ? $data['permissionsContext']
                    : ManagedPermissionsContext::fromArray($data['permissionsContext']))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'account' => $this->account,
            'resolved' => $this->resolved,
            'values' => $this->values instanceof ManagedSettingsValues ? $this->values->toArray() : $this->values,
            'meta' => $this->meta instanceof ManagedSettingsMeta ? $this->meta->toArray() : $this->meta,
            'layers' => array_map(static fn ($layer) => $layer instanceof ManagedSettingsLayer ? $layer->toArray() : $layer, $this->layers),
            'diagnostics' => array_map(static fn ($diagnostic) => $diagnostic instanceof ManagedSettingsDiagnostic ? $diagnostic->toArray() : $diagnostic, $this->diagnostics),
            'permissionsContext' => $this->permissionsContext?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
