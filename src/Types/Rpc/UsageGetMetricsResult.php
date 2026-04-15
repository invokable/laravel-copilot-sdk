<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

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
     * @param  int  $sessionStartTime  Session start timestamp (epoch milliseconds)
     * @param  CodeChanges  $codeChanges  Aggregated code change metrics
     * @param  array<string, ModelMetric>  $modelMetrics  Per-model token and request metrics, keyed by model identifier
     * @param  int  $lastCallInputTokens  Input tokens from the most recent main-agent API call
     * @param  int  $lastCallOutputTokens  Output tokens from the most recent main-agent API call
     * @param  ?string  $currentModel  Currently active model identifier
     */
    public function __construct(
        public float $totalPremiumRequestCost,
        public int $totalUserRequests,
        public float $totalApiDurationMs,
        public int $sessionStartTime,
        public CodeChanges $codeChanges,
        public array $modelMetrics,
        public int $lastCallInputTokens,
        public int $lastCallOutputTokens,
        public ?string $currentModel = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $modelMetrics = [];
        foreach ($data['modelMetrics'] ?? [] as $key => $metric) {
            $modelMetrics[$key] = ModelMetric::fromArray($metric);
        }

        return new self(
            totalPremiumRequestCost: (float) $data['totalPremiumRequestCost'],
            totalUserRequests: $data['totalUserRequests'],
            totalApiDurationMs: (float) $data['totalApiDurationMs'],
            sessionStartTime: $data['sessionStartTime'],
            codeChanges: CodeChanges::fromArray($data['codeChanges']),
            modelMetrics: $modelMetrics,
            lastCallInputTokens: $data['lastCallInputTokens'],
            lastCallOutputTokens: $data['lastCallOutputTokens'],
            currentModel: $data['currentModel'] ?? null,
        );
    }

    public function toArray(): array
    {
        $modelMetrics = [];
        foreach ($this->modelMetrics as $key => $metric) {
            $modelMetrics[$key] = $metric->toArray();
        }

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
        ], fn ($v) => $v !== null);
    }
}
