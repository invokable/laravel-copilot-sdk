<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Observed state of a single factory subagent.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryAgentSummary implements Arrayable
{
    /**
     * @param  string  $agentId  Subagent instance identifier.
     * @param  string  $toolCallId  Tool call that spawned the subagent.
     * @param  string  $runId  Factory run identifier that owns the subagent.
     * @param  ?string  $phaseId  Phase the subagent was spawned in, or null.
     * @param  string  $label  Human-readable subagent label.
     * @param  string  $agentType  Subagent type.
     * @param  string  $status  Current subagent status.
     * @param  int  $activeMs  Accumulated active execution time, in milliseconds.
     * @param  ?string  $requestedModel  Model requested for the subagent.
     * @param  ?string  $resolvedModel  Model the subagent actually resolved to.
     * @param  ?int  $startedAt  Epoch milliseconds when the subagent started.
     * @param  ?int  $completedAt  Epoch milliseconds when the subagent completed.
     * @param  ?string  $activity  Latest activity description.
     */
    public function __construct(
        public string $agentId,
        public string $toolCallId,
        public string $runId,
        public ?string $phaseId,
        public string $label,
        public string $agentType,
        public string $status,
        public int $activeMs,
        public ?string $requestedModel = null,
        public ?string $resolvedModel = null,
        public ?int $startedAt = null,
        public ?int $completedAt = null,
        public ?string $activity = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            agentId: Arr::string($data, 'agentId', ''),
            toolCallId: Arr::string($data, 'toolCallId', ''),
            runId: Arr::string($data, 'runId', ''),
            phaseId: $data['phaseId'] ?? null,
            label: Arr::string($data, 'label', ''),
            agentType: Arr::string($data, 'agentType', ''),
            status: Arr::string($data, 'status', ''),
            activeMs: Arr::integer($data, 'activeMs', 0),
            requestedModel: $data['requestedModel'] ?? null,
            resolvedModel: $data['resolvedModel'] ?? null,
            startedAt: $data['startedAt'] ?? null,
            completedAt: $data['completedAt'] ?? null,
            activity: $data['activity'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'agentId' => $this->agentId,
            'toolCallId' => $this->toolCallId,
            'runId' => $this->runId,
            'phaseId' => $this->phaseId,
            'label' => $this->label,
            'agentType' => $this->agentType,
            'status' => $this->status,
            'activeMs' => $this->activeMs,
            'requestedModel' => $this->requestedModel,
            'resolvedModel' => $this->resolvedModel,
            'startedAt' => $this->startedAt,
            'completedAt' => $this->completedAt,
            'activity' => $this->activity,
        ], fn ($v) => $v !== null);
    }
}
