<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AiCreditsStatus;

/**
 * Result of session usage metrics query.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class UsageGetMetricsResult implements Arrayable
{
    /**
     * @param  float  $totalPremiumRequestCost  Total user-initiated premium request cost across all models
     * @param  int  $totalUserRequests  Raw count of user-initiated API requests
     * @param  float  $totalApiDurationMs  Total time spent in model API calls (milliseconds)
     * @param  int|string  $sessionStartTime  Session start timestamp (legacy epoch milliseconds or upstream ISO 8601 string)
     * @param  CodeChanges  $codeChanges  Aggregated code change metrics
     * @param  array<string, ModelMetric>  $modelMetrics  Per-model token and request metrics, keyed by model identifier
     * @param  array<string, UsageMetricsAgentMetric|array>|null  $agentMetrics  Per-agent usage, keyed by agent instance
     * @param  array<string, UsageMetricsTokenDetail|array>|null  $tokenDetails  Session-wide per-token-type counts
     * @param  int  $lastCallInputTokens  Input tokens from the most recent main-agent API call
     * @param  int  $lastCallOutputTokens  Output tokens from the most recent main-agent API call
     * @param  ?string  $currentModel  Currently active model identifier
     */
    public function __construct(
        public float $totalPremiumRequestCost,
        public int $totalUserRequests,
        public float $totalApiDurationMs,
        public int|string $sessionStartTime,
        public CodeChanges $codeChanges,
        public array $modelMetrics,
        public int $lastCallInputTokens,
        public int $lastCallOutputTokens,
        public ?string $currentModel = null,
        public AiCreditsStatus|string|null $aiCreditsStatus = null,
        public ?array $providerModelMetrics = null,
        public ?array $agentMetrics = null,
        public ?array $tokenDetails = null,
        public int|float|null $totalNanoAiu = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $modelMetrics = [];
        foreach ($data['modelMetrics'] ?? [] as $key => $metric) {
            $modelMetrics[$key] = ModelMetric::fromArray($metric);
        }

        $aiCreditsStatus = $data['aiCreditsStatus'] ?? null;
        $providerModelMetrics = isset($data['providerModelMetrics'])
            ? array_map(
                static fn (UsageMetricsProviderModelMetric|array $metric) => $metric instanceof UsageMetricsProviderModelMetric
                    ? $metric
                    : UsageMetricsProviderModelMetric::fromArray($metric),
                $data['providerModelMetrics'],
            )
            : null;
        $agentMetrics = isset($data['agentMetrics'])
            ? array_map(
                static fn (UsageMetricsAgentMetric|array $metric) => $metric instanceof UsageMetricsAgentMetric
                    ? $metric
                    : UsageMetricsAgentMetric::fromArray($metric),
                $data['agentMetrics'],
            )
            : null;
        $tokenDetails = isset($data['tokenDetails'])
            ? array_map(
                static fn (UsageMetricsTokenDetail|array $detail) => $detail instanceof UsageMetricsTokenDetail
                    ? $detail
                    : UsageMetricsTokenDetail::fromArray($detail),
                $data['tokenDetails'],
            )
            : null;
        $sessionStartTime = $data['sessionStartTime'] ?? 0;

        return new self(
            totalPremiumRequestCost: is_int($data['totalPremiumRequestCost'] ?? null) ? (float) Arr::integer($data, 'totalPremiumRequestCost') : Arr::float($data, 'totalPremiumRequestCost'),
            totalUserRequests: Arr::integer($data, 'totalUserRequests'),
            totalApiDurationMs: isset($data['totalApiDurationMs'])
                ? (float) $data['totalApiDurationMs']
                : (float) ($data['totalApiDuration'] ?? 0),
            sessionStartTime: is_numeric($sessionStartTime) ? (int) $sessionStartTime : (string) $sessionStartTime,
            codeChanges: CodeChanges::fromArray($data['codeChanges']),
            modelMetrics: $modelMetrics,
            lastCallInputTokens: Arr::integer($data, 'lastCallInputTokens'),
            lastCallOutputTokens: Arr::integer($data, 'lastCallOutputTokens'),
            currentModel: $data['currentModel'] ?? null,
            aiCreditsStatus: $aiCreditsStatus === null
                ? null
                : (AiCreditsStatus::tryFrom($aiCreditsStatus) ?? $aiCreditsStatus),
            providerModelMetrics: $providerModelMetrics,
            agentMetrics: $agentMetrics,
            tokenDetails: $tokenDetails,
            totalNanoAiu: isset($data['totalNanoAiu']) && is_numeric($data['totalNanoAiu'])
                ? $data['totalNanoAiu'] + 0
                : null,
        );
    }

    public function toArray(): array
    {
        $modelMetrics = [];
        foreach ($this->modelMetrics as $key => $metric) {
            $modelMetrics[$key] = $metric->toArray();
        }
        $agentMetrics = $this->agentMetrics === null
            ? null
            : array_map(
                static fn (UsageMetricsAgentMetric|array $metric) => $metric instanceof UsageMetricsAgentMetric
                    ? $metric->toArray()
                    : $metric,
                $this->agentMetrics,
            );
        $tokenDetails = $this->tokenDetails === null
            ? null
            : array_map(
                static fn (UsageMetricsTokenDetail|array $detail) => $detail instanceof UsageMetricsTokenDetail
                    ? $detail->toArray()
                    : $detail,
                $this->tokenDetails,
            );

        return array_filter([
            'totalPremiumRequestCost' => $this->totalPremiumRequestCost,
            'totalUserRequests' => $this->totalUserRequests,
            'totalApiDurationMs' => $this->totalApiDurationMs,
            'sessionStartTime' => $this->sessionStartTime,
            'codeChanges' => $this->codeChanges->toArray(),
            'modelMetrics' => $modelMetrics,
            'lastCallInputTokens' => $this->lastCallInputTokens,
            'lastCallOutputTokens' => $this->lastCallOutputTokens,
            'currentModel' => $this->currentModel,
            'aiCreditsStatus' => $this->aiCreditsStatus instanceof AiCreditsStatus
                ? $this->aiCreditsStatus->value
                : $this->aiCreditsStatus,
            'providerModelMetrics' => $this->providerModelMetrics === null
                ? null
                : array_map(
                    static fn (UsageMetricsProviderModelMetric|array $metric) => $metric instanceof UsageMetricsProviderModelMetric
                        ? $metric->toArray()
                        : $metric,
                    $this->providerModelMetrics,
                ),
            'agentMetrics' => $agentMetrics,
            'tokenDetails' => $tokenDetails,
            'totalNanoAiu' => $this->totalNanoAiu,
        ], fn ($v) => $v !== null);
    }
}
