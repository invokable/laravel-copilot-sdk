<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ModelInfo;

/**
 * List of available models.
 */
readonly class ModelList implements Arrayable
{
    /**
     * @param  array<ModelInfo>  $models  List of available models with full metadata
     * @param  ?array<ImageToolModel>  $imageModels  Runtime image-model catalog, separate from conversation models
     */
    public function __construct(
        public array $models,
        public ?array $imageModels = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            models: array_map(
                fn (array $model) => ModelInfo::fromArray($model),
                $data['models'] ?? [],
            ),
            imageModels: isset($data['imageModels'])
                ? array_map(fn (array $model) => ImageToolModel::fromArray($model), $data['imageModels'])
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'models' => array_map(fn (ModelInfo $model) => $model->toArray(), $this->models),
            'imageModels' => $this->imageModels !== null
                ? array_map(fn (ImageToolModel $model) => $model->toArray(), $this->imageModels)
                : null,
        ], fn ($value) => $value !== null);
    }
}
