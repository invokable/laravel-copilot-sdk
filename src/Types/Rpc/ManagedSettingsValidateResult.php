<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Result of validating a managed-settings document. */
readonly class ManagedSettingsValidateResult implements Arrayable
{
    /** @param array<ManagedSettingsDiagnostic|array> $diagnostics Validation findings. */
    public function __construct(
        public bool $valid = false,
        public mixed $settings = null,
        public array $diagnostics = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            valid: (bool) ($data['valid'] ?? false),
            settings: $data['settings'] ?? null,
            diagnostics: array_map(static fn ($diagnostic) => $diagnostic instanceof ManagedSettingsDiagnostic ? $diagnostic : ManagedSettingsDiagnostic::fromArray($diagnostic), $data['diagnostics'] ?? []),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'valid' => $this->valid,
            'settings' => $this->settings,
            'diagnostics' => array_map(static fn ($diagnostic) => $diagnostic instanceof ManagedSettingsDiagnostic ? $diagnostic->toArray() : $diagnostic, $this->diagnostics),
        ], fn ($value) => $value !== null);
    }
}
