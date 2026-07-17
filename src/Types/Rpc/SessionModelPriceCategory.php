<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\ModelPickerPriceCategory;

/**
 * Cost-category metadata for a CAPI model.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionModelPriceCategory implements Arrayable
{
    public function __construct(
        public string $id,
        public ModelPickerPriceCategory $priceCategory,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
            priceCategory: ModelPickerPriceCategory::from(Arr::string($data, 'priceCategory')),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'priceCategory' => $this->priceCategory->value,
        ];
    }
}
