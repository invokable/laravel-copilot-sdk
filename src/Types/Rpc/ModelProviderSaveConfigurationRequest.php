<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\NamedProviderConfig;
use Revolution\Copilot\Types\ProviderModelConfig;

/**
 * Provider and model configuration to save for future sessions.
 *
 * @experimental
 */
readonly class ModelProviderSaveConfigurationRequest implements Arrayable
{
    public function __construct(
        public NamedProviderConfig $provider,
        public ProviderModelConfig $model,
    ) {}

    public static function fromArray(array $data): self
    {
        $provider = $data['provider'] ?? [];
        $model = $data['model'] ?? [];

        return new self(
            provider: $provider instanceof NamedProviderConfig
                ? $provider
                : NamedProviderConfig::fromArray($provider),
            model: $model instanceof ProviderModelConfig
                ? $model
                : ProviderModelConfig::fromArray($model),
        );
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider->toArray(),
            'model' => $this->model->toArray(),
        ];
    }
}
