<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SessionLimitPredictionClientType;
use Revolution\Copilot\Enums\SessionLimitPredictionSource;
use Revolution\Copilot\Enums\SessionLimitPredictionTier;

/**
 * Explainable AI-credit session-limit prediction.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionLimitPredictionDetails implements Arrayable
{
    /**
     * @param  SessionLimitPredictionClientType|string  $clientType  Client population used for the prediction baseline.
     * @param  string  $modelId  Model identifier used for lookup.
     * @param  SessionLimitPredictionSource|string  $source  Baseline fallback level used to create the prediction.
     * @param  string  $sourceKey  Key matched at the source level, such as a model id, family id, or `global`.
     * @param  array<int, SessionLimitPredictionTierOption>  $tiers  Ordered usage tiers and their AI-credit caps.
     * @param  SessionLimitPredictionBaselineData|array  $baselineData  Baseline data provenance for the prediction.
     * @param  SessionLimitPredictionTier|string  $recommendedTier  Recommended usage tier.
     * @param  float  $recommendedCap  Recommended maximum AI credits for this session.
     * @param  ?string  $family  Resolved model family when known.
     */
    public function __construct(
        public SessionLimitPredictionClientType|string $clientType,
        public string $modelId,
        public SessionLimitPredictionSource|string $source,
        public string $sourceKey,
        public array $tiers,
        public SessionLimitPredictionBaselineData|array $baselineData,
        public SessionLimitPredictionTier|string $recommendedTier,
        public float $recommendedCap,
        public ?string $family = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $baselineData = $data['baselineData'] ?? [];

        return new self(
            clientType: SessionLimitPredictionClientType::tryFrom($data['clientType'] ?? '') ?? $data['clientType'],
            modelId: Arr::string($data, 'modelId'),
            source: SessionLimitPredictionSource::tryFrom($data['source'] ?? '') ?? $data['source'],
            sourceKey: Arr::string($data, 'sourceKey'),
            tiers: array_map(
                fn ($tier) => $tier instanceof SessionLimitPredictionTierOption ? $tier : SessionLimitPredictionTierOption::fromArray($tier),
                $data['tiers'] ?? [],
            ),
            baselineData: $baselineData instanceof SessionLimitPredictionBaselineData ? $baselineData : SessionLimitPredictionBaselineData::fromArray($baselineData),
            recommendedTier: SessionLimitPredictionTier::tryFrom($data['recommendedTier'] ?? '') ?? $data['recommendedTier'],
            recommendedCap: Arr::float($data, 'recommendedCap', 0.0),
            family: $data['family'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'clientType' => $this->clientType instanceof SessionLimitPredictionClientType ? $this->clientType->value : $this->clientType,
            'modelId' => $this->modelId,
            'source' => $this->source instanceof SessionLimitPredictionSource ? $this->source->value : $this->source,
            'sourceKey' => $this->sourceKey,
            'tiers' => array_map(
                fn (SessionLimitPredictionTierOption|array $tier) => $tier instanceof SessionLimitPredictionTierOption ? $tier->toArray() : $tier,
                $this->tiers,
            ),
            'baselineData' => $this->baselineData instanceof SessionLimitPredictionBaselineData ? $this->baselineData->toArray() : $this->baselineData,
            'recommendedTier' => $this->recommendedTier instanceof SessionLimitPredictionTier ? $this->recommendedTier->value : $this->recommendedTier,
            'recommendedCap' => $this->recommendedCap,
            'family' => $this->family,
        ], fn ($v) => $v !== null);
    }
}
