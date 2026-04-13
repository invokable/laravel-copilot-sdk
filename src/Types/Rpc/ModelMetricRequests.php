<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request count and cost metrics for a model.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelMetricRequests implements Arrayable
{
    /**
     * @param  int  $count  Number of API requests made with this model
     * @param  float  $cost  User-initiated premium request cost (with multiplier applied)
     */
    public function __construct(
        public int $count,
        public float $cost,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            count: $data['count'],
            cost: (float) $data['cost'],
        );
    }

    public function toArray(): array
    {
        return [
            'count' => $this->count,
            'cost' => $this->cost,
        ];
    }
}
