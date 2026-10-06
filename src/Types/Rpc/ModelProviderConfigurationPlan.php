<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\NamedProviderConfig;
use Revolution\Copilot\Types\ProviderModelConfig;

/** Provider/model configuration prepared from a discovered instance and model. */
readonly class ModelProviderConfigurationPlan implements Arrayable
{
    public function __construct(
        public NamedProviderConfig|array $provider,
        public ProviderModelConfig|array $model,
        public string $providerDisposition,
        public string $modelDisposition,
        public string $selectionId,
        public array $warnings = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            provider: isset($data['provider'])
                ? NamedProviderConfig::fromArray($data['provider'])
                : [],
            model: isset($data['model'])
                ? ProviderModelConfig::fromArray($data['model'])
                : [],
            providerDisposition: $data['providerDisposition'] ?? '',
            modelDisposition: $data['modelDisposition'] ?? '',
            selectionId: $data['selectionId'] ?? '',
            warnings: array_map(
                fn (array $warning) => ModelProviderWarning::fromArray($warning),
                $data['warnings'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        $provider = $this->provider instanceof NamedProviderConfig
            ? $this->provider->toArray()
            : $this->provider;
        $model = $this->model instanceof ProviderModelConfig
            ? $this->model->toArray()
            : $this->model;

        return [
            'provider' => $provider,
            'model' => $model,
            'providerDisposition' => $this->providerDisposition,
            'modelDisposition' => $this->modelDisposition,
            'selectionId' => $this->selectionId,
            'warnings' => array_map(
                fn (ModelProviderWarning $warning) => $warning->toArray(),
                $this->warnings,
            ),
        ];
    }
}
