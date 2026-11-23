<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class SessionUiSnapshotResult implements Arrayable
{
    /**
     * @param  string[]  $startupPrompts
     * @param  UIPendingUserInput[]  $pendingUserInputRequests
     * @param  UIPendingElicitation[]  $pendingElicitationRequests
     * @param  UIPendingPlanApproval[]  $pendingExitPlanModeRequests
     * @param  UIServiceTask[]  $serviceTasks
     * @param  UIBackgroundAgentTask[]  $sidekickBackgroundTasks
     * @param  ?array<string, int>  $primaryEventCounts
     */
    public function __construct(
        public array $startupPrompts,
        public array $pendingUserInputRequests,
        public array $pendingElicitationRequests,
        public array $pendingExitPlanModeRequests,
        public array $serviceTasks,
        public int $initializingServiceCount,
        public array $sidekickBackgroundTasks,
        public bool $agentTurnActive,
        public bool $processing,
        public bool $supportsMcpApps,
        public ?bool $hasPendingRepoHooks = null,
        public ?bool $isExperimentalMode = null,
        public ?bool $isSessionTelemetryEnabled = null,
        public ?bool $hasActiveResumeWork = null,
        public ?array $primaryEventCounts = null,
        public ?int $workingDirectoryRevision = null,
        public ?UIPluginActivationSnapshot $pluginActivationSnapshot = null,
        public ?UIPluginActivationPolicy $pluginActivationPolicy = null,
        private bool $pluginActivationSnapshotPresent = false,
        private bool $pluginActivationPolicyPresent = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            startupPrompts: array_values(Arr::array($data, 'startupPrompts', [])),
            hasPendingRepoHooks: isset($data['hasPendingRepoHooks']) ? Arr::boolean($data, 'hasPendingRepoHooks') : null,
            pendingUserInputRequests: array_map(
                static fn (array $request): UIPendingUserInput => UIPendingUserInput::fromArray($request),
                array_values(Arr::array($data, 'pendingUserInputRequests', [])),
            ),
            pendingElicitationRequests: array_map(
                static fn (array $request): UIPendingElicitation => UIPendingElicitation::fromArray($request),
                array_values(Arr::array($data, 'pendingElicitationRequests', [])),
            ),
            pendingExitPlanModeRequests: array_map(
                static fn (array $request): UIPendingPlanApproval => UIPendingPlanApproval::fromArray($request),
                array_values(Arr::array($data, 'pendingExitPlanModeRequests', [])),
            ),
            serviceTasks: array_map(
                static fn (array $task): UIServiceTask => UIServiceTask::fromArray($task),
                array_values(Arr::array($data, 'serviceTasks', [])),
            ),
            initializingServiceCount: Arr::integer($data, 'initializingServiceCount', 0),
            sidekickBackgroundTasks: array_map(
                static fn (array $task): UIBackgroundAgentTask => UIBackgroundAgentTask::fromArray($task),
                array_values(Arr::array($data, 'sidekickBackgroundTasks', [])),
            ),
            agentTurnActive: Arr::boolean($data, 'agentTurnActive', false),
            processing: Arr::boolean($data, 'processing', false),
            isExperimentalMode: isset($data['isExperimentalMode']) ? Arr::boolean($data, 'isExperimentalMode') : null,
            isSessionTelemetryEnabled: isset($data['isSessionTelemetryEnabled']) ? Arr::boolean($data, 'isSessionTelemetryEnabled') : null,
            hasActiveResumeWork: isset($data['hasActiveResumeWork']) ? Arr::boolean($data, 'hasActiveResumeWork') : null,
            primaryEventCounts: isset($data['primaryEventCounts']) ? Arr::array($data, 'primaryEventCounts') : null,
            workingDirectoryRevision: isset($data['workingDirectoryRevision']) ? Arr::integer($data, 'workingDirectoryRevision') : null,
            supportsMcpApps: Arr::boolean($data, 'supportsMcpApps', false),
            pluginActivationSnapshot: is_array($data['pluginActivationSnapshot'] ?? null)
                ? UIPluginActivationSnapshot::fromArray($data['pluginActivationSnapshot'])
                : null,
            pluginActivationPolicy: is_array($data['pluginActivationPolicy'] ?? null)
                ? UIPluginActivationPolicy::fromArray($data['pluginActivationPolicy'])
                : null,
            pluginActivationSnapshotPresent: array_key_exists('pluginActivationSnapshot', $data),
            pluginActivationPolicyPresent: array_key_exists('pluginActivationPolicy', $data),
        );
    }

    public function toArray(): array
    {
        $data = array_filter([
            'startupPrompts' => $this->startupPrompts,
            'hasPendingRepoHooks' => $this->hasPendingRepoHooks,
            'pendingUserInputRequests' => array_map(static fn (UIPendingUserInput $request): array => $request->toArray(), $this->pendingUserInputRequests),
            'pendingElicitationRequests' => array_map(static fn (UIPendingElicitation $request): array => $request->toArray(), $this->pendingElicitationRequests),
            'pendingExitPlanModeRequests' => array_map(static fn (UIPendingPlanApproval $request): array => $request->toArray(), $this->pendingExitPlanModeRequests),
            'serviceTasks' => array_map(static fn (UIServiceTask $task): array => $task->toArray(), $this->serviceTasks),
            'initializingServiceCount' => $this->initializingServiceCount,
            'sidekickBackgroundTasks' => array_map(static fn (UIBackgroundAgentTask $task): array => $task->toArray(), $this->sidekickBackgroundTasks),
            'agentTurnActive' => $this->agentTurnActive,
            'processing' => $this->processing,
            'isExperimentalMode' => $this->isExperimentalMode,
            'isSessionTelemetryEnabled' => $this->isSessionTelemetryEnabled,
            'hasActiveResumeWork' => $this->hasActiveResumeWork,
            'primaryEventCounts' => $this->primaryEventCounts,
            'workingDirectoryRevision' => $this->workingDirectoryRevision,
            'supportsMcpApps' => $this->supportsMcpApps,
            'pluginActivationSnapshot' => $this->pluginActivationSnapshot?->toArray(),
            'pluginActivationPolicy' => $this->pluginActivationPolicy?->toArray(),
        ], static fn ($value): bool => $value !== null);

        if ($this->pluginActivationSnapshotPresent || $this->pluginActivationSnapshot !== null) {
            $data['pluginActivationSnapshot'] = $this->pluginActivationSnapshot?->toArray();
        }

        if ($this->pluginActivationPolicyPresent || $this->pluginActivationPolicy !== null) {
            $data['pluginActivationPolicy'] = $this->pluginActivationPolicy?->toArray();
        }

        return $data;
    }
}
