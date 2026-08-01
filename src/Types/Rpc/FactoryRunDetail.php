<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FactoryRunStatus;

/**
 * Detailed observation of a factory run, including phases, agents, and progress.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryRunDetail implements Arrayable
{
    /**
     * @param  string  $runId  Factory run identifier.
     * @param  string  $factoryName  Registered factory name.
     * @param  string  $description  Human-readable run description.
     * @param  FactoryRunStatus|string  $status  Current or terminal factory run status.
     * @param  int  $revision  Monotonic observation revision.
     * @param  int  $createdAt  Epoch milliseconds when the run was created.
     * @param  ?int  $startedAt  Epoch milliseconds when the run started, or null.
     * @param  int  $updatedAt  Epoch milliseconds of the last update.
     * @param  ?int  $completedAt  Epoch milliseconds when the run completed, or null.
     * @param  FactoryCurrentPhase|array|null  $currentPhase  Phase the run is currently executing, or null.
     * @param  int  $declaredPhaseCount  Number of declared phases.
     * @param  int  $liveAgentCount  Number of subagents still live.
     * @param  int  $totalSpawnedAgentCount  Total number of subagents spawned.
     * @param  FactoryRunConsumed|array  $consumed  Resources consumed so far.
     * @param  FactoryDeclaredLimits|array  $declaredLimits  Declared per-run resource ceilings.
     * @param  FactoryDeclaredLimits|array|null  $approved  Approved per-run resource ceilings, or null.
     * @param  int  $observedAt  Epoch milliseconds this snapshot was observed.
     * @param  ?int  $activeSegmentStartedAt  Epoch milliseconds the current active segment started, or null.
     * @param  FactoryRunTerminal|array|null  $terminal  Terminal outcome details, or null when not terminal.
     * @param  array<FactoryPhaseObservation>  $phases  Observed declared phases.
     * @param  array<FactoryAgentSummary>  $agents  Observed subagents.
     * @param  FactoryProgressPage|array  $progress  Latest progress page.
     */
    public function __construct(
        public string $runId,
        public string $factoryName,
        public string $description,
        public FactoryRunStatus|string $status,
        public int $revision,
        public int $createdAt,
        public ?int $startedAt,
        public int $updatedAt,
        public ?int $completedAt,
        public FactoryCurrentPhase|array|null $currentPhase,
        public int $declaredPhaseCount,
        public int $liveAgentCount,
        public int $totalSpawnedAgentCount,
        public FactoryRunConsumed|array $consumed,
        public FactoryDeclaredLimits|array $declaredLimits,
        public FactoryDeclaredLimits|array|null $approved,
        public int $observedAt,
        public ?int $activeSegmentStartedAt,
        public FactoryRunTerminal|array|null $terminal,
        public array $phases,
        public array $agents,
        public FactoryProgressPage|array $progress,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            runId: Arr::string($data, 'runId', ''),
            factoryName: Arr::string($data, 'factoryName', ''),
            description: Arr::string($data, 'description', ''),
            status: $data['status'] instanceof FactoryRunStatus ? $data['status'] : FactoryRunStatus::from($data['status']),
            revision: Arr::integer($data, 'revision', 0),
            createdAt: Arr::integer($data, 'createdAt', 0),
            startedAt: $data['startedAt'] ?? null,
            updatedAt: Arr::integer($data, 'updatedAt', 0),
            completedAt: $data['completedAt'] ?? null,
            currentPhase: isset($data['currentPhase']) ? FactoryCurrentPhase::fromArray($data['currentPhase']) : null,
            declaredPhaseCount: Arr::integer($data, 'declaredPhaseCount', 0),
            liveAgentCount: Arr::integer($data, 'liveAgentCount', 0),
            totalSpawnedAgentCount: Arr::integer($data, 'totalSpawnedAgentCount', 0),
            consumed: FactoryRunConsumed::fromArray($data['consumed'] ?? []),
            declaredLimits: FactoryDeclaredLimits::fromArray($data['declaredLimits'] ?? []),
            approved: isset($data['approved']) ? FactoryDeclaredLimits::fromArray($data['approved']) : null,
            observedAt: Arr::integer($data, 'observedAt', 0),
            activeSegmentStartedAt: $data['activeSegmentStartedAt'] ?? null,
            terminal: isset($data['terminal']) ? FactoryRunTerminal::fromArray($data['terminal']) : null,
            phases: array_map(
                fn ($phase) => $phase instanceof FactoryPhaseObservation ? $phase : FactoryPhaseObservation::fromArray($phase),
                $data['phases'] ?? [],
            ),
            agents: array_map(
                fn ($agent) => $agent instanceof FactoryAgentSummary ? $agent : FactoryAgentSummary::fromArray($agent),
                $data['agents'] ?? [],
            ),
            progress: FactoryProgressPage::fromArray($data['progress'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'runId' => $this->runId,
            'factoryName' => $this->factoryName,
            'description' => $this->description,
            'status' => $this->status instanceof FactoryRunStatus ? $this->status->value : $this->status,
            'revision' => $this->revision,
            'createdAt' => $this->createdAt,
            'startedAt' => $this->startedAt,
            'updatedAt' => $this->updatedAt,
            'completedAt' => $this->completedAt,
            'currentPhase' => $this->currentPhase instanceof FactoryCurrentPhase ? $this->currentPhase->toArray() : $this->currentPhase,
            'declaredPhaseCount' => $this->declaredPhaseCount,
            'liveAgentCount' => $this->liveAgentCount,
            'totalSpawnedAgentCount' => $this->totalSpawnedAgentCount,
            'consumed' => $this->consumed instanceof FactoryRunConsumed ? $this->consumed->toArray() : $this->consumed,
            'declaredLimits' => $this->declaredLimits instanceof FactoryDeclaredLimits ? $this->declaredLimits->toArray() : $this->declaredLimits,
            'approved' => $this->approved instanceof FactoryDeclaredLimits ? $this->approved->toArray() : $this->approved,
            'observedAt' => $this->observedAt,
            'activeSegmentStartedAt' => $this->activeSegmentStartedAt,
            'terminal' => $this->terminal instanceof FactoryRunTerminal ? $this->terminal->toArray() : $this->terminal,
            'phases' => array_map(fn (FactoryPhaseObservation $phase) => $phase->toArray(), $this->phases),
            'agents' => array_map(fn (FactoryAgentSummary $agent) => $agent->toArray(), $this->agents),
            'progress' => $this->progress instanceof FactoryProgressPage ? $this->progress->toArray() : $this->progress,
        ];
    }
}
