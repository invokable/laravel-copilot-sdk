<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ProviderModelConfig;

/** @experimental */
readonly class ProvidersConfigReadResult implements Arrayable
{
    /**
     * @param  SavedProviderConfigurationProvider[]  $providers
     * @param  ProviderModelConfig[]  $models
     */
    public function __construct(
        public array $providers = [],
        public array $models = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            providers: array_map(
                fn ($provider) => $provider instanceof SavedProviderConfigurationProvider
                    ? $provider
                    : SavedProviderConfigurationProvider::fromArray($provider),
                $data['providers'] ?? [],
            ),
            models: array_map(
                fn ($model) => $model instanceof ProviderModelConfig ? $model : ProviderModelConfig::fromArray($model),
                $data['models'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'providers' => array_map(fn (SavedProviderConfigurationProvider $provider) => $provider->toArray(), $this->providers),
            'models' => array_map(fn (ProviderModelConfig $model) => $model->toArray(), $this->models),
        ];
    }
}
