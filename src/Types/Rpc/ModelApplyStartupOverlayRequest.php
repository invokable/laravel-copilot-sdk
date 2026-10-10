<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AutoTier;

/** Managed and repository model overrides applied to a session at startup. */
readonly class ModelApplyStartupOverlayRequest implements Arrayable
{
    public function __construct(
        public ?string $deviceManagedModel = null,
        public ?string $serverManagedModel = null,
        public ?string $policyHelperModel = null,
        public AutoTier|string|null $autoTier = null,
        public ?string $repoModel = null,
        public ?string $repoModelProviderId = null,
        public ?string $repoReasoningEffort = null,
        public ?string $repoContextTier = null,
        public ?string $repoAutoTier = null,
        public ?string $cliModel = null,
        public ?bool $deferredResume = null,
        public ?string $managedReasoningEffort = null,
        public ?string $managedContextTier = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $autoTier = $data['autoTier'] ?? null;

        return new self(
            deviceManagedModel: $data['deviceManagedModel'] ?? null,
            serverManagedModel: $data['serverManagedModel'] ?? null,
            policyHelperModel: $data['policyHelperModel'] ?? null,
            autoTier: $autoTier === null ? null : (AutoTier::tryFrom($autoTier) ?? $autoTier),
            repoModel: $data['repoModel'] ?? null,
            repoModelProviderId: $data['repoModelProviderId'] ?? null,
            repoReasoningEffort: $data['repoReasoningEffort'] ?? null,
            repoContextTier: $data['repoContextTier'] ?? null,
            repoAutoTier: $data['repoAutoTier'] ?? null,
            cliModel: $data['cliModel'] ?? null,
            deferredResume: $data['deferredResume'] ?? null,
            managedReasoningEffort: $data['managedReasoningEffort'] ?? null,
            managedContextTier: $data['managedContextTier'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'deviceManagedModel' => $this->deviceManagedModel,
            'serverManagedModel' => $this->serverManagedModel,
            'policyHelperModel' => $this->policyHelperModel,
            'autoTier' => $this->autoTier instanceof AutoTier ? $this->autoTier->value : $this->autoTier,
            'repoModel' => $this->repoModel,
            'repoModelProviderId' => $this->repoModelProviderId,
            'repoReasoningEffort' => $this->repoReasoningEffort,
            'repoContextTier' => $this->repoContextTier,
            'repoAutoTier' => $this->repoAutoTier,
            'cliModel' => $this->cliModel,
            'deferredResume' => $this->deferredResume,
            'managedReasoningEffort' => $this->managedReasoningEffort,
            'managedContextTier' => $this->managedContextTier,
        ], fn ($value) => $value !== null);
    }
}
