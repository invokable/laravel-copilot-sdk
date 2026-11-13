<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A terminal classification of one unit in a recorded trajectory.
 *
 * @experimental
 */
readonly class TaskAnalyticsTrajectoryClassification implements Arrayable
{
    /**
     * @param  array<string, string>  $labels
     * @param  array<string, int|float>  $metrics
     */
    public function __construct(
        public string $classifier,
        public int $classifierVersion,
        public string $scope,
        public string $unitId,
        public int $ordinal,
        public string $closeReason,
        public string $status,
        public array $labels,
        public array $metrics,
        public bool $textTruncated,
        public int $unresolvedBoundaries,
        public ?int $taxonomyVersion = null,
        public ?string $anchorEventId = null,
        public ?int $firstMemberOrdinal = null,
        public ?int $lastMemberOrdinal = null,
        public ?string $model = null,
        public ?string $interactionId = null,
        public ?string $modelSelectionReason = null,
        public ?string $reasoningEffort = null,
        public int|float|null $inputTokens = null,
        public int|float|null $outputTokens = null,
        public int|float|null $cachedInputTokens = null,
        public ?string $errorKind = null,
        public ?int $httpStatus = null,
        public ?bool $retryable = null,
        public int|float|null $retryAfterSeconds = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            classifier: Arr::string($data, 'classifier'),
            classifierVersion: Arr::integer($data, 'classifierVersion'),
            scope: Arr::string($data, 'scope'),
            unitId: Arr::string($data, 'unitId'),
            ordinal: Arr::integer($data, 'ordinal'),
            closeReason: Arr::string($data, 'closeReason'),
            status: Arr::string($data, 'status'),
            labels: Arr::array($data, 'labels', []),
            metrics: Arr::array($data, 'metrics', []),
            textTruncated: Arr::boolean($data, 'textTruncated'),
            unresolvedBoundaries: Arr::integer($data, 'unresolvedBoundaries'),
            taxonomyVersion: isset($data['taxonomyVersion']) ? Arr::integer($data, 'taxonomyVersion') : null,
            anchorEventId: isset($data['anchorEventId']) ? Arr::string($data, 'anchorEventId') : null,
            firstMemberOrdinal: isset($data['firstMemberOrdinal']) ? Arr::integer($data, 'firstMemberOrdinal') : null,
            lastMemberOrdinal: isset($data['lastMemberOrdinal']) ? Arr::integer($data, 'lastMemberOrdinal') : null,
            model: isset($data['model']) ? Arr::string($data, 'model') : null,
            interactionId: isset($data['interactionId']) ? Arr::string($data, 'interactionId') : null,
            modelSelectionReason: isset($data['modelSelectionReason']) ? Arr::string($data, 'modelSelectionReason') : null,
            reasoningEffort: isset($data['reasoningEffort']) ? Arr::string($data, 'reasoningEffort') : null,
            inputTokens: $data['inputTokens'] ?? null,
            outputTokens: $data['outputTokens'] ?? null,
            cachedInputTokens: $data['cachedInputTokens'] ?? null,
            errorKind: isset($data['errorKind']) ? Arr::string($data, 'errorKind') : null,
            httpStatus: isset($data['httpStatus']) ? Arr::integer($data, 'httpStatus') : null,
            retryable: isset($data['retryable']) ? Arr::boolean($data, 'retryable') : null,
            retryAfterSeconds: $data['retryAfterSeconds'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'classifier' => $this->classifier,
            'classifierVersion' => $this->classifierVersion,
            'taxonomyVersion' => $this->taxonomyVersion,
            'scope' => $this->scope,
            'unitId' => $this->unitId,
            'ordinal' => $this->ordinal,
            'anchorEventId' => $this->anchorEventId,
            'closeReason' => $this->closeReason,
            'status' => $this->status,
            'labels' => $this->labels,
            'metrics' => $this->metrics,
            'textTruncated' => $this->textTruncated,
            'unresolvedBoundaries' => $this->unresolvedBoundaries,
            'firstMemberOrdinal' => $this->firstMemberOrdinal,
            'lastMemberOrdinal' => $this->lastMemberOrdinal,
            'model' => $this->model,
            'interactionId' => $this->interactionId,
            'modelSelectionReason' => $this->modelSelectionReason,
            'reasoningEffort' => $this->reasoningEffort,
            'inputTokens' => $this->inputTokens,
            'outputTokens' => $this->outputTokens,
            'cachedInputTokens' => $this->cachedInputTokens,
            'errorKind' => $this->errorKind,
            'httpStatus' => $this->httpStatus,
            'retryable' => $this->retryable,
            'retryAfterSeconds' => $this->retryAfterSeconds,
        ], fn ($value) => $value !== null);
    }
}
