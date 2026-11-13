<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for retrospectively classifying a recorded Copilot or ATIF trajectory.
 *
 * @experimental This request sends supplied content for model processing through GitHub CAPI.
 *               Callers must authorize that processing and validate archive provenance.
 */
readonly class TaskAnalyticsClassifyTrajectoryRequest implements Arrayable
{
    /**
     * @param  array<int, mixed>  $events
     * @param  array<int, string>|null  $classifiers
     */
    public function __construct(
        public array $events,
        public ?array $classifiers = null,
        public ?int $maxConcurrentRequests = null,
        public TaskAnalyticsJudge|array|null $judge = null,
        public ?bool $validateOnly = null,
        public TaskAnalyticsClassifyTrajectoryResult|array|null $previousResult = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $judge = $data['judge'] ?? null;
        $previousResult = $data['previousResult'] ?? null;

        return new self(
            events: Arr::array($data, 'events', []),
            classifiers: isset($data['classifiers']) ? array_values(Arr::array($data, 'classifiers')) : null,
            maxConcurrentRequests: isset($data['maxConcurrentRequests']) ? Arr::integer($data, 'maxConcurrentRequests') : null,
            judge: is_array($judge) ? TaskAnalyticsJudge::fromArray($judge) : $judge,
            validateOnly: isset($data['validateOnly']) ? Arr::boolean($data, 'validateOnly') : null,
            previousResult: is_array($previousResult) ? TaskAnalyticsClassifyTrajectoryResult::fromArray($previousResult) : $previousResult,
        );
    }

    public function toArray(): array
    {
        $judge = $this->judge instanceof TaskAnalyticsJudge
            ? $this->judge->toArray()
            : $this->judge;
        $previousResult = $this->previousResult instanceof TaskAnalyticsClassifyTrajectoryResult
            ? $this->previousResult->toArray()
            : $this->previousResult;

        return array_filter([
            'events' => $this->events,
            'classifiers' => $this->classifiers,
            'maxConcurrentRequests' => $this->maxConcurrentRequests,
            'judge' => $judge,
            'validateOnly' => $this->validateOnly,
            'previousResult' => $previousResult,
        ], fn ($value) => $value !== null);
    }
}
