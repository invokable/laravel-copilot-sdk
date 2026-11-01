<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Effective managed settings and contributing channels for a live session. */
readonly class ManagedSettingsResolvedData implements Arrayable
{
    /**
     * @param  array<string>  $managedKeys
     * @param  array<string, mixed>|null  $policyHelperCapabilities
     * @param  array<string, mixed>|null  $policyHelperFailure
     */
    public function __construct(
        public bool $bypassPermissionsDisabled,
        public bool $deviceManaged,
        public bool $failClosed,
        public array $managedKeys,
        public bool $serverManaged,
        public string $source,
        public ?bool $clientManaged = null,
        public ?bool $permissionsAllowIntersected = null,
        public ManagedPermissionsContext|array|null $permissionsContext = null,
        public ?array $policyHelperCapabilities = null,
        public ?array $policyHelperFailure = null,
        public ?bool $policyHelperManaged = null,
        public ?bool $sandboxEnabledByUndeterminedPolicy = null,
        public mixed $settings = null,
        private bool $settingsProvided = false,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            bypassPermissionsDisabled: $data['bypassPermissionsDisabled'] ?? false,
            deviceManaged: $data['deviceManaged'] ?? false,
            failClosed: $data['failClosed'] ?? false,
            managedKeys: $data['managedKeys'] ?? [],
            serverManaged: $data['serverManaged'] ?? false,
            source: $data['source'] ?? 'none',
            clientManaged: $data['clientManaged'] ?? null,
            permissionsAllowIntersected: $data['permissionsAllowIntersected'] ?? null,
            permissionsContext: isset($data['permissionsContext'])
                ? ($data['permissionsContext'] instanceof ManagedPermissionsContext
                    ? $data['permissionsContext']
                    : ManagedPermissionsContext::fromArray($data['permissionsContext']))
                : null,
            policyHelperCapabilities: $data['policyHelperCapabilities'] ?? null,
            policyHelperFailure: $data['policyHelperFailure'] ?? null,
            policyHelperManaged: $data['policyHelperManaged'] ?? null,
            sandboxEnabledByUndeterminedPolicy: $data['sandboxEnabledByUndeterminedPolicy'] ?? null,
            settings: $data['settings'] ?? null,
            settingsProvided: array_key_exists('settings', $data),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'bypassPermissionsDisabled' => $this->bypassPermissionsDisabled,
            'clientManaged' => $this->clientManaged,
            'deviceManaged' => $this->deviceManaged,
            'failClosed' => $this->failClosed,
            'managedKeys' => $this->managedKeys,
            'permissionsAllowIntersected' => $this->permissionsAllowIntersected,
            'permissionsContext' => $this->permissionsContext instanceof ManagedPermissionsContext
                ? $this->permissionsContext->toArray()
                : $this->permissionsContext,
            'policyHelperCapabilities' => $this->policyHelperCapabilities,
            'policyHelperFailure' => $this->policyHelperFailure,
            'policyHelperManaged' => $this->policyHelperManaged,
            'sandboxEnabledByUndeterminedPolicy' => $this->sandboxEnabledByUndeterminedPolicy,
            'serverManaged' => $this->serverManaged,
            'settings' => $this->settings,
            'source' => $this->source,
        ], fn ($value, $key) => $value !== null || ($key === 'settings' && $this->settingsProvided), ARRAY_FILTER_USE_BOTH);
    }
}
