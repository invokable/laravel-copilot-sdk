<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class DiscoveredModelList implements Arrayable
{
    /** @param DiscoveredModel[] $models */
    public function __construct(
        public array $models = [],
        public ?ModelProviderOperationOutcome $outcome = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            models: array_map(fn (array $model) => DiscoveredModel::fromArray($model), $data['models'] ?? []),
            outcome: isset($data['outcome']) ? ModelProviderOperationOutcome::fromArray($data['outcome']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'models' => array_map(fn (DiscoveredModel $model) => $model->toArray(), $this->models),
            'outcome' => $this->outcome?->toArray(),
        ], static fn ($value) => $value !== null);
    }
}
