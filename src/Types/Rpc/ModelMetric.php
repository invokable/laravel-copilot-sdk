<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AiCreditsStatus;

/**
 * Per-model metrics including request counts and token usage.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelMetric implements Arrayable
{
    /**
     * @param  ModelMetricRequests  $requests  Request count and cost metrics for this model
     * @param  ModelMetricUsage  $usage  Token usage metrics for this model
     * @param  array<string, UsageMetricsModelMetricTokenDetail|array>|null  $tokenDetails  Per-token-type counts for this model
     */
    public function __construct(
        public ModelMetricRequests $requests,
        public ModelMetricUsage $usage,
        public AiCreditsStatus|string|null $aiCreditsStatus = null,
        public ?string $cacheExpiresAt = null,
        public ?array $tokenDetails = null,
        public int|float|null $totalNanoAiu = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $aiCreditsStatus = $data['aiCreditsStatus'] ?? null;
        $tokenDetails = isset($data['tokenDetails'])
            ? array_map(
                static fn (UsageMetricsModelMetricTokenDetail|array $detail) => $detail instanceof UsageMetricsModelMetricTokenDetail
                    ? $detail
                    : UsageMetricsModelMetricTokenDetail::fromArray($detail),
                $data['tokenDetails'],
            )
            : null;

        return new self(
            requests: ModelMetricRequests::fromArray($data['requests']),
            usage: ModelMetricUsage::fromArray($data['usage']),
            aiCreditsStatus: $aiCreditsStatus === null
                ? null
                : (AiCreditsStatus::tryFrom($aiCreditsStatus) ?? $aiCreditsStatus),
            cacheExpiresAt: $data['cacheExpiresAt'] ?? null,
            tokenDetails: $tokenDetails,
            totalNanoAiu: isset($data['totalNanoAiu']) && is_numeric($data['totalNanoAiu'])
                ? $data['totalNanoAiu'] + 0
                : null,
        );
    }

    public function toArray(): array
    {
        $tokenDetails = $this->tokenDetails === null
            ? null
            : array_map(
                static fn (UsageMetricsModelMetricTokenDetail|array $detail) => $detail instanceof UsageMetricsModelMetricTokenDetail
                    ? $detail->toArray()
                    : $detail,
                $this->tokenDetails,
            );

        return array_filter([
            'requests' => $this->requests->toArray(),
            'usage' => $this->usage->toArray(),
            'aiCreditsStatus' => $this->aiCreditsStatus instanceof AiCreditsStatus
                ? $this->aiCreditsStatus->value
                : $this->aiCreditsStatus,
            'cacheExpiresAt' => $this->cacheExpiresAt,
            'tokenDetails' => $tokenDetails,
            'totalNanoAiu' => $this->totalNanoAiu,
        ], fn ($value) => $value !== null);
    }
}
