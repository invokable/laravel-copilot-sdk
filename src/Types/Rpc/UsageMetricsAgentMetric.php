<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Usage attributed to one agent instance. */
readonly class UsageMetricsAgentMetric implements Arrayable
{
    /** @param array<string, ModelMetric|array> $modelMetrics */
    public function __construct(
        public array $modelMetrics = [],
        public int|float $totalApiDurationMs = 0,
        public int|float $totalNanoAiu = 0,
        public ?string $agentDisplayName = null,
        public ?string $agentName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $modelMetrics = [];
        foreach ($data['modelMetrics'] ?? [] as $model => $metric) {
            $modelMetrics[$model] = $metric instanceof ModelMetric
                ? $metric
                : ModelMetric::fromArray($metric);
        }

        return new self(
            modelMetrics: $modelMetrics,
            totalApiDurationMs: self::number($data['totalApiDurationMs'] ?? 0),
            totalNanoAiu: self::number($data['totalNanoAiu'] ?? 0),
            agentDisplayName: $data['agentDisplayName'] ?? null,
            agentName: $data['agentName'] ?? null,
        );
    }

    public function toArray(): array
    {
        $modelMetrics = [];
        foreach ($this->modelMetrics as $model => $metric) {
            $modelMetrics[$model] = $metric instanceof ModelMetric ? $metric->toArray() : $metric;
        }

        return array_filter([
            'agentDisplayName' => $this->agentDisplayName,
            'agentName' => $this->agentName,
            'modelMetrics' => $modelMetrics,
            'totalApiDurationMs' => $this->totalApiDurationMs,
            'totalNanoAiu' => $this->totalNanoAiu,
        ], static fn ($value) => $value !== null);
    }

    private static function number(mixed $value): int|float
    {
        return is_numeric($value) ? $value + 0 : 0;
    }
}
