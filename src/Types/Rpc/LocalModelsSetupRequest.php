<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Setup intent for one catalog model.
 *
 * @experimental
 */
readonly class LocalModelsSetupRequest implements Arrayable
{
    public function __construct(
        public LocalModelDescriptor $model,
        public string $intent,
    ) {}

    public static function fromArray(array $data): self
    {
        $model = $data['model'] ?? [];

        return new self(
            model: $model instanceof LocalModelDescriptor
                ? $model
                : LocalModelDescriptor::fromArray(is_array($model) ? $model : []),
            intent: $data['intent'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'model' => $this->model->toArray(),
            'intent' => $this->intent,
        ];
    }
}
