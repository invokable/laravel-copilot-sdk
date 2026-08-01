<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionLimitPredictionClientType;

/**
 * Parameters for predicting an AI-credit session limit. Omitting `modelId` uses
 * the session's currently selected model.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionLimitPredictionPredictRequest implements Arrayable
{
    /**
     * @param  ?string  $modelId  Optional model identifier override. If omitted, the session's current model is used.
     * @param  SessionLimitPredictionClientType|string|null  $clientType  Client population used for the prediction baseline.
     */
    public function __construct(
        public ?string $modelId = null,
        public SessionLimitPredictionClientType|string|null $clientType = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $clientType = isset($data['clientType'])
            ? (SessionLimitPredictionClientType::tryFrom($data['clientType']) ?? $data['clientType'])
            : null;

        return new self(
            modelId: $data['modelId'] ?? null,
            clientType: $clientType,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'modelId' => $this->modelId,
            'clientType' => $this->clientType instanceof SessionLimitPredictionClientType ? $this->clientType->value : $this->clientType,
        ], fn ($v) => $v !== null);
    }
}
