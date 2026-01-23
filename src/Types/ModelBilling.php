<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Model billing information.
 */
readonly class ModelBilling implements Arrayable
{
    public function __construct(
        /** Billing multiplier */
        public float $multiplier,
    ) {}

    /**
     * Create from array.
     *
     * @param  array{multiplier: float}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            multiplier: (float) $data['multiplier'],
        );
    }

    /**
     * Convert to array.
     *
     * @return array{multiplier: float}
     */
    public function toArray(): array
    {
        return [
            'multiplier' => $this->multiplier,
        ];
    }
}
