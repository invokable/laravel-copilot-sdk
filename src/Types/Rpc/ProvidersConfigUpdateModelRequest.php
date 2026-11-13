<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ProviderModelConfig;

/** @experimental */
readonly class ProvidersConfigUpdateModelRequest implements Arrayable
{
    public function __construct(public ProviderModelConfig $model) {}

    public static function fromArray(array $data): self
    {
        $model = $data['model'] ?? [];

        return new self(model: $model instanceof ProviderModelConfig ? $model : ProviderModelConfig::fromArray($model));
    }

    public function toArray(): array
    {
        return ['model' => $this->model->toArray()];
    }
}
