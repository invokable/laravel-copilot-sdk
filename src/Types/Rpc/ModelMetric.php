<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

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
     */
    public function __construct(
        public ModelMetricRequests $requests,
        public ModelMetricUsage $usage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requests: ModelMetricRequests::fromArray($data['requests']),
            usage: ModelMetricUsage::fromArray($data['usage']),
        );
    }

    public function toArray(): array
    {
        return [
            'requests' => $this->requests->toArray(),
            'usage' => $this->usage->toArray(),
        ];
    }
}
