<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FactoryPhaseStatus;

/**
 * Observed state of a single declared factory phase.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryPhaseObservation implements Arrayable
{
    /**
     * @param  string  $id  Stable phase identifier.
     * @param  ?int  $ordinal  Zero-based declared phase ordinal, or null for an ad-hoc phase.
     * @param  string  $title  Phase title.
     * @param  FactoryPhaseStatus|string  $status  Derived lifecycle state of the phase.
     * @param  int  $lastEnteredRunAttempt  Run attempt during which the phase was last entered.
     * @param  int  $entryCount  Number of times the phase has been entered.
     * @param  int  $accumulatedActiveMs  Total active time accumulated across entries, in milliseconds.
     * @param  int  $currentActiveMs  Active time accumulated in the current entry, in milliseconds.
     * @param  int  $totalAgentCount  Total number of subagents spawned in the phase.
     * @param  int  $liveAgentCount  Number of subagents still live in the phase.
     * @param  ?string  $detail  Optional phase detail.
     * @param  ?int  $startedAt  Epoch milliseconds when the phase was first entered.
     * @param  ?int  $completedAt  Epoch milliseconds when the phase was closed.
     */
    public function __construct(
        public string $id,
        public ?int $ordinal,
        public string $title,
        public FactoryPhaseStatus|string $status,
        public int $lastEnteredRunAttempt,
        public int $entryCount,
        public int $accumulatedActiveMs,
        public int $currentActiveMs,
        public int $totalAgentCount,
        public int $liveAgentCount,
        public ?string $detail = null,
        public ?int $startedAt = null,
        public ?int $completedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id', ''),
            ordinal: $data['ordinal'] ?? null,
            title: Arr::string($data, 'title', ''),
            status: $data['status'] instanceof FactoryPhaseStatus ? $data['status'] : FactoryPhaseStatus::from($data['status']),
            lastEnteredRunAttempt: Arr::integer($data, 'lastEnteredRunAttempt', 0),
            entryCount: Arr::integer($data, 'entryCount', 0),
            accumulatedActiveMs: Arr::integer($data, 'accumulatedActiveMs', 0),
            currentActiveMs: Arr::integer($data, 'currentActiveMs', 0),
            totalAgentCount: Arr::integer($data, 'totalAgentCount', 0),
            liveAgentCount: Arr::integer($data, 'liveAgentCount', 0),
            detail: $data['detail'] ?? null,
            startedAt: $data['startedAt'] ?? null,
            completedAt: $data['completedAt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'ordinal' => $this->ordinal,
            'title' => $this->title,
            'status' => $this->status instanceof FactoryPhaseStatus ? $this->status->value : $this->status,
            'lastEnteredRunAttempt' => $this->lastEnteredRunAttempt,
            'entryCount' => $this->entryCount,
            'accumulatedActiveMs' => $this->accumulatedActiveMs,
            'currentActiveMs' => $this->currentActiveMs,
            'totalAgentCount' => $this->totalAgentCount,
            'liveAgentCount' => $this->liveAgentCount,
            'detail' => $this->detail,
            'startedAt' => $this->startedAt,
            'completedAt' => $this->completedAt,
        ], fn ($v) => $v !== null);
    }
}
