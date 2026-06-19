<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverride;

/**
 * A BYOK model definition that references a NamedProviderConfig by name
 * and is added to the session's selectable model list.
 *
 * Each model has three identities:
 *  - id: the provider-local model id, unique within its provider. The session-wide
 *    selection id is the provider-qualified `provider/id`.
 *  - modelId: the well-known behavior base model used for capability/config lookup.
 *    Defaults to id.
 *  - wireModel: the model name actually sent to the provider API for inference.
 *    Defaults to id.
 *
 * @experimental This type is part of an experimental multi-provider BYOK surface
 * and may change or be removed in future SDK or CLI releases.
 */
readonly class ProviderModelConfig implements Arrayable
{
    /**
     * @param  string  $id  Provider-local model id, unique within its provider.
     * @param  string  $provider  Name of the NamedProviderConfig that serves this model.
     * @param  ?string  $wireModel  The model name sent to the provider API for inference. Defaults to id.
     * @param  ?string  $modelId  Well-known base model id used for behavior/capability/config lookup. Defaults to id.
     * @param  ?string  $name  Display name for model pickers. Defaults to the provider-qualified selection id.
     * @param  ?int  $maxPromptTokens  Maximum prompt/input tokens for the model.
     * @param  ?int  $maxContextWindowTokens  Maximum context window tokens for the model.
     * @param  ?int  $maxOutputTokens  Maximum output tokens for the model.
     * @param  ModelCapabilitiesOverride|array|null  $capabilities  Optional capability overrides.
     */
    public function __construct(
        public string $id,
        public string $provider,
        public ?string $wireModel = null,
        public ?string $modelId = null,
        public ?string $name = null,
        public ?int $maxPromptTokens = null,
        public ?int $maxContextWindowTokens = null,
        public ?int $maxOutputTokens = null,
        public ModelCapabilitiesOverride|array|null $capabilities = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $capabilities = null;
        if (isset($data['capabilities'])) {
            $capabilities = $data['capabilities'] instanceof ModelCapabilitiesOverride
                ? $data['capabilities']
                : ModelCapabilitiesOverride::fromArray($data['capabilities']);
        }

        return new self(
            id: $data['id'] ?? '',
            provider: $data['provider'] ?? '',
            wireModel: $data['wireModel'] ?? null,
            modelId: $data['modelId'] ?? null,
            name: $data['name'] ?? null,
            maxPromptTokens: isset($data['maxPromptTokens']) ? (int) $data['maxPromptTokens'] : null,
            maxContextWindowTokens: isset($data['maxContextWindowTokens']) ? (int) $data['maxContextWindowTokens'] : null,
            maxOutputTokens: isset($data['maxOutputTokens']) ? (int) $data['maxOutputTokens'] : null,
            capabilities: $capabilities,
        );
    }

    public function toArray(): array
    {
        $capabilities = $this->capabilities instanceof ModelCapabilitiesOverride
            ? $this->capabilities->toArray()
            : $this->capabilities;

        return array_filter([
            'id' => $this->id,
            'provider' => $this->provider,
            'wireModel' => $this->wireModel,
            'modelId' => $this->modelId,
            'name' => $this->name,
            'maxPromptTokens' => $this->maxPromptTokens,
            'maxContextWindowTokens' => $this->maxContextWindowTokens,
            'maxOutputTokens' => $this->maxOutputTokens,
            'capabilities' => $capabilities,
        ], fn ($v) => $v !== null);
    }
}
