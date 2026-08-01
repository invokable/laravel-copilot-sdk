<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionLimitPredictionResultKind;
use Revolution\Copilot\Enums\SessionLimitPredictionUnavailableReason;

/**
 * Prediction result. Available results include prediction details; unavailable
 * results include an explicit reason.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionLimitPredictionResult implements Arrayable
{
    /**
     * @param  SessionLimitPredictionResultKind|string  $kind  Discriminator for the result variant.
     * @param  SessionLimitPredictionDetails|array|null  $prediction  Prediction details when available.
     * @param  SessionLimitPredictionUnavailableReason|string|null  $reason  Reason a prediction could not be computed.
     */
    public function __construct(
        public SessionLimitPredictionResultKind|string $kind,
        public SessionLimitPredictionDetails|array|null $prediction = null,
        public SessionLimitPredictionUnavailableReason|string|null $reason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $prediction = $data['prediction'] ?? null;

        $reason = isset($data['reason'])
            ? (SessionLimitPredictionUnavailableReason::tryFrom($data['reason']) ?? $data['reason'])
            : null;

        return new self(
            kind: SessionLimitPredictionResultKind::tryFrom($data['kind'] ?? '') ?? $data['kind'],
            prediction: $prediction !== null
                ? ($prediction instanceof SessionLimitPredictionDetails ? $prediction : SessionLimitPredictionDetails::fromArray($prediction))
                : null,
            reason: $reason,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind instanceof SessionLimitPredictionResultKind ? $this->kind->value : $this->kind,
            'prediction' => $this->prediction instanceof SessionLimitPredictionDetails ? $this->prediction->toArray() : $this->prediction,
            'reason' => $this->reason instanceof SessionLimitPredictionUnavailableReason ? $this->reason->value : $this->reason,
        ], fn ($v) => $v !== null);
    }
}
