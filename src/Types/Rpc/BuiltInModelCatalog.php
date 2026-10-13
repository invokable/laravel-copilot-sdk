<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Built-in model catalog entries.
 */
readonly class BuiltInModelCatalog implements Arrayable
{
    /**
     * @param  BuiltInModelCatalogEntry[]  $models
     */
    public function __construct(
        public array $models,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            models: array_map(
                fn (array $model): BuiltInModelCatalogEntry => BuiltInModelCatalogEntry::fromArray($model),
                $data['models'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'models' => array_map(fn (BuiltInModelCatalogEntry $model): array => $model->toArray(), $this->models),
        ];
    }
}
