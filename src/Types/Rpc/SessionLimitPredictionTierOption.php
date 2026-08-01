<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SessionLimitPredictionTier;

/**
 * Semantic usage tier and its AI-credit cap.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionLimitPredictionTierOption implements Arrayable
{
    /**
     * @param  SessionLimitPredictionTier|string  $tier  Semantic usage tier.
     * @param  float  $cap  AI-credit cap for this tier.
     */
    public function __construct(
        public SessionLimitPredictionTier|string $tier,
        public float $cap,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tier: SessionLimitPredictionTier::tryFrom($data['tier'] ?? '') ?? $data['tier'],
            cap: Arr::float($data, 'cap', 0.0),
        );
    }

    public function toArray(): array
    {
        return [
            'tier' => $this->tier instanceof SessionLimitPredictionTier ? $this->tier->value : $this->tier,
            'cap' => $this->cap,
        ];
    }
}
