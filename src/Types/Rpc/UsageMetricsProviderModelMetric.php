<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Usage for a provider/model pair, without merging matching model IDs. */
readonly class UsageMetricsProviderModelMetric implements Arrayable
{
    public function __construct(
        public ModelMetric|array|null $metrics,
        public ?string $modelId,
        public ModelProviderRef|array|null $provider,
        public ?string $modelDisplayName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            metrics: isset($data['metrics'])
                ? ($data['metrics'] instanceof ModelMetric
                    ? $data['metrics']
                    : ModelMetric::fromArray($data['metrics']))
                : null,
            modelId: $data['modelId'] ?? null,
            provider: isset($data['provider'])
                ? ($data['provider'] instanceof ModelProviderRef
                    ? $data['provider']
                    : ModelProviderRef::fromArray($data['provider']))
                : null,
            modelDisplayName: $data['modelDisplayName'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'metrics' => $this->metrics instanceof ModelMetric
                ? $this->metrics->toArray()
                : $this->metrics,
            'modelDisplayName' => $this->modelDisplayName,
            'modelId' => $this->modelId,
            'provider' => $this->provider instanceof ModelProviderRef
                ? $this->provider->toArray()
                : $this->provider,
        ], static fn ($value, $key) => $key === 'modelId' || $key === 'provider' || $value !== null, ARRAY_FILTER_USE_BOTH);
    }
}
