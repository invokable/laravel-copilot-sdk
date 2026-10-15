<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * List of model token price units.
 */
readonly class ModelTokenPriceUnitList implements Arrayable
{
    /**
     * @param  array<int, ModelTokenPriceUnit>  $units
     */
    public function __construct(
        public array $units,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            units: array_map(
                fn (array $unit) => ModelTokenPriceUnit::fromArray($unit),
                $data['units'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'units' => array_map(fn (ModelTokenPriceUnit $unit) => $unit->toArray(), $this->units),
        ];
    }
}
