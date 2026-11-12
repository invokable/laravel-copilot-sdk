<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ProviderModelConfig;
use SensitiveParameter;

/** @experimental */
readonly class ProvidersConfigAddModelRequest implements Arrayable
{
    public function __construct(
        public SavedProviderConfigurationProviderInput $provider,
        public ProviderModelConfig $model,
        #[SensitiveParameter]
        public ?string $credential = null,
    ) {}

    public static function fromArray(#[SensitiveParameter] array $data): self
    {
        $provider = $data['provider'] ?? [];
        $model = $data['model'] ?? [];

        return new self(
            provider: $provider instanceof SavedProviderConfigurationProviderInput
                ? $provider
                : SavedProviderConfigurationProviderInput::fromArray($provider),
            model: $model instanceof ProviderModelConfig
                ? $model
                : ProviderModelConfig::fromArray($model),
            credential: $data['credential'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'provider' => $this->provider->toArray(),
            'model' => $this->model->toArray(),
            'credential' => $this->credential,
        ], fn ($value) => $value !== null);
    }
}
