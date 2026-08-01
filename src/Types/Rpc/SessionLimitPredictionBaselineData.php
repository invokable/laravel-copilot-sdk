<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Baseline data provenance for a session limit prediction.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionLimitPredictionBaselineData implements Arrayable
{
    /**
     * @param  string  $windowStart  Start of the baseline data slice.
     * @param  string  $windowEnd  End of the baseline data slice.
     */
    public function __construct(
        public string $windowStart,
        public string $windowEnd,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            windowStart: Arr::string($data, 'windowStart'),
            windowEnd: Arr::string($data, 'windowEnd'),
        );
    }

    public function toArray(): array
    {
        return [
            'windowStart' => $this->windowStart,
            'windowEnd' => $this->windowEnd,
        ];
    }
}
