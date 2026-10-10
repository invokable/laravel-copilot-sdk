<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionQuotaPlanTier;

/** Session-owned quota and account projection. */
readonly class SessionQuotaProjection implements Arrayable
{
    /**
     * @param  array<string, SessionQuotaSnapshot|array>  $snapshots
     * @param  array<ProviderQuotaState|array>  $providerQuotas
     */
    public function __construct(
        public array $snapshots = [],
        public array $providerQuotas = [],
        public bool $isFreeUser = false,
        public bool $isTbbUser = false,
        public SessionQuotaPlanTier|string $planTier = SessionQuotaPlanTier::UNKNOWN,
        public bool $premiumRequestsBillable = false,
        public bool $modelCostColumnVisible = false,
        public bool $delegateAvailable = false,
        public bool $canSignupForCopilotFree = false,
        public bool $dynamicWorkflowsEnabled = false,
        public bool $dynamicWorkflowsUiVisible = false,
        public ?string $upgradeUrl = null,
        public SessionQuotaDelegateWarning|array|null $delegateWarning = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $planTier = $data['planTier'] ?? 'unknown';

        $snapshots = [];
        foreach ($data['snapshots'] ?? [] as $key => $snapshot) {
            $snapshots[$key] = $snapshot instanceof SessionQuotaSnapshot
                ? $snapshot
                : SessionQuotaSnapshot::fromArray($snapshot);
        }

        return new static(
            snapshots: $snapshots,
            providerQuotas: array_map(
                static fn (ProviderQuotaState|array $quota) => $quota instanceof ProviderQuotaState
                    ? $quota
                    : ProviderQuotaState::fromArray($quota),
                $data['providerQuotas'] ?? [],
            ),
            isFreeUser: (bool) ($data['isFreeUser'] ?? false),
            isTbbUser: (bool) ($data['isTbbUser'] ?? false),
            planTier: SessionQuotaPlanTier::tryFrom($planTier) ?? $planTier,
            premiumRequestsBillable: (bool) ($data['premiumRequestsBillable'] ?? false),
            modelCostColumnVisible: (bool) ($data['modelCostColumnVisible'] ?? false),
            delegateAvailable: (bool) ($data['delegateAvailable'] ?? false),
            canSignupForCopilotFree: (bool) ($data['canSignupForCopilotFree'] ?? false),
            dynamicWorkflowsEnabled: (bool) ($data['dynamicWorkflowsEnabled'] ?? false),
            dynamicWorkflowsUiVisible: (bool) ($data['dynamicWorkflowsUiVisible'] ?? false),
            upgradeUrl: $data['upgradeUrl'] ?? null,
            delegateWarning: isset($data['delegateWarning'])
                ? ($data['delegateWarning'] instanceof SessionQuotaDelegateWarning
                    ? $data['delegateWarning']
                    : SessionQuotaDelegateWarning::fromArray($data['delegateWarning']))
                : null,
        );
    }

    public function toArray(): array
    {
        $snapshots = [];
        foreach ($this->snapshots as $key => $snapshot) {
            $snapshots[$key] = $snapshot instanceof SessionQuotaSnapshot
                ? $snapshot->toArray()
                : $snapshot;
        }

        return array_filter([
            'snapshots' => $snapshots,
            'providerQuotas' => array_map(
                static fn (ProviderQuotaState|array $quota) => $quota instanceof ProviderQuotaState
                    ? $quota->toArray()
                    : $quota,
                $this->providerQuotas,
            ),
            'isFreeUser' => $this->isFreeUser,
            'isTbbUser' => $this->isTbbUser,
            'planTier' => $this->planTier instanceof SessionQuotaPlanTier ? $this->planTier->value : $this->planTier,
            'premiumRequestsBillable' => $this->premiumRequestsBillable,
            'modelCostColumnVisible' => $this->modelCostColumnVisible,
            'delegateAvailable' => $this->delegateAvailable,
            'canSignupForCopilotFree' => $this->canSignupForCopilotFree,
            'dynamicWorkflowsEnabled' => $this->dynamicWorkflowsEnabled,
            'dynamicWorkflowsUiVisible' => $this->dynamicWorkflowsUiVisible,
            'upgradeUrl' => $this->upgradeUrl,
            'delegateWarning' => $this->delegateWarning instanceof SessionQuotaDelegateWarning
                ? $this->delegateWarning->toArray()
                : $this->delegateWarning,
        ], fn ($value) => $value !== null);
    }
}
