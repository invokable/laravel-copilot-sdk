<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A model returned by this connection's catalog.
 *
 * @experimental
 */
readonly class LocalModelsModelRequest implements Arrayable
{
    public function __construct(
        public LocalModelDescriptor $model,
    ) {}

    public static function fromArray(array $data): static
    {
        $model = $data['model'] ?? [];

        return new static(
            model: $model instanceof LocalModelDescriptor
                ? $model
                : LocalModelDescriptor::fromArray(is_array($model) ? $model : []),
        );
    }

    public function toArray(): array
    {
        return ['model' => $this->model->toArray()];
    }
}
