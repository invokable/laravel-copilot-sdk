<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * The applied host allowlist and effective session model policy after intersection.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelSetAllowedModelsResult implements Arrayable
{
    /**
     * @param  ?array  $allowedModels  Normalized host allowlist. Omitted when the host restriction was cleared,
     *                                 or when a relay client does not return the host policy.
     * @param  ?array  $effectiveAllowedModels  Effective exact IDs or repository policy patterns after applying
     *                                          the host restriction. Omitted by relay clients that do not return
     *                                          the host policy.
     * @param  ?string  $fallbackModel  Effective deterministic fallback model, when the policy defines one.
     * @param  ?string  $modelId  Selected session model after reconciling a now-disallowed concrete selection.
     */
    public function __construct(
        public ?array $allowedModels = null,
        public ?array $effectiveAllowedModels = null,
        public ?string $fallbackModel = null,
        public ?string $modelId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            allowedModels: $data['allowedModels'] ?? null,
            effectiveAllowedModels: $data['effectiveAllowedModels'] ?? null,
            fallbackModel: Arr::get($data, 'fallbackModel'),
            modelId: Arr::get($data, 'modelId'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'allowedModels' => $this->allowedModels,
            'effectiveAllowedModels' => $this->effectiveAllowedModels,
            'fallbackModel' => $this->fallbackModel,
            'modelId' => $this->modelId,
        ], fn ($v) => $v !== null);
    }
}
