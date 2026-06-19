<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\ModelBillingTokenPrices;

/**
 * Model billing information.
 */
readonly class ModelBilling implements Arrayable
{
    /**
     * @param  ?float  $multiplier  Billing cost multiplier relative to the base rate
     * @param  ModelBillingTokenPrices|null  $tokenPrices  Token-level pricing information for this model
     */
    public function __construct(
        public ?float $multiplier = null,
        public ?ModelBillingTokenPrices $tokenPrices = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            multiplier: isset($data['multiplier']) ? (float) $data['multiplier'] : null,
            tokenPrices: isset($data['tokenPrices']) ? ModelBillingTokenPrices::fromArray($data['tokenPrices']) : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'multiplier' => $this->multiplier,
            'tokenPrices' => $this->tokenPrices?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
