<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ModelInfo;

/**
 * Result of listing available models.
 */
readonly class ModelsListResult implements Arrayable
{
    /**
     * @param  array<ModelInfo>  $models  List of available models with full metadata
     */
    public function __construct(
        public array $models,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            models: array_map(
                fn (array $model) => ModelInfo::fromArray($model),
                $data['models'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'models' => array_map(fn (ModelInfo $model) => $model->toArray(), $this->models),
        ];
    }
}
