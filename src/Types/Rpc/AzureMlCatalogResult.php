<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Eligible Azure ML models and redacted catalog warnings.
 *
 * @experimental
 */
readonly class AzureMlCatalogResult implements Arrayable
{
    /**
     * @param  array<AzureMlModel>  $models
     * @param  array<AzureMlCatalogWarning>  $warnings
     */
    public function __construct(
        public array $models,
        public array $warnings,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            models: array_map(
                fn (array $model) => AzureMlModel::fromArray($model),
                $data['models'],
            ),
            warnings: array_map(
                fn (array $warning) => AzureMlCatalogWarning::fromArray($warning),
                $data['warnings'],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'models' => array_map(fn (AzureMlModel $model) => $model->toArray(), $this->models),
            'warnings' => array_map(fn (AzureMlCatalogWarning $warning) => $warning->toArray(), $this->warnings),
        ];
    }
}
