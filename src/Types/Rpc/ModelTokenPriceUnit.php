<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\TokenPriceUnit;

/**
 * Token price unit for a model.
 */
readonly class ModelTokenPriceUnit implements Arrayable
{
    /**
     * @param  TokenPriceUnit|string  $unit  Billing unit for model token prices.
     */
    public function __construct(
        public string $modelId,
        public TokenPriceUnit|string $unit,
    ) {}

    public static function fromArray(array $data): self
    {
        $unit = $data['unit'] ?? '';

        return new self(
            modelId: $data['modelId'] ?? '',
            unit: is_string($unit) ? (TokenPriceUnit::tryFrom($unit) ?? $unit) : '',
        );
    }

    public function toArray(): array
    {
        return [
            'modelId' => $this->modelId,
            'unit' => $this->unit instanceof TokenPriceUnit ? $this->unit->value : $this->unit,
        ];
    }
}
