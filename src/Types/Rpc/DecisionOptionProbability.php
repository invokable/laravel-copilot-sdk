<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Probability assigned to one choice option.
 *
 * @experimental
 */
readonly class DecisionOptionProbability implements Arrayable
{
    public function __construct(
        public string $value,
        public float $probability,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'],
            probability: $data['probability'],
        );
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'probability' => $this->probability,
        ];
    }
}
