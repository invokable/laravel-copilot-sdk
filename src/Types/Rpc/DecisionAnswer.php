<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The answer to one decision question.
 *
 * @experimental
 */
readonly class DecisionAnswer implements Arrayable
{
    /**
     * @param  ?array<int, DecisionOptionProbability>  $probabilities
     */
    public function __construct(
        public string $kind,
        public string $name,
        public ?float $probability = null,
        public ?string $value = null,
        public ?bool $tied = null,
        public ?float $confidence = null,
        public ?float $vendorConfidence = null,
        public ?array $probabilities = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return match ($data['kind']) {
            'boolean' => new self(
                kind: 'boolean',
                name: $data['name'],
                probability: $data['probability'],
            ),
            'choice' => new self(
                kind: 'choice',
                name: $data['name'],
                value: $data['value'],
                tied: $data['tied'],
                confidence: $data['confidence'],
                vendorConfidence: $data['vendorConfidence'] ?? null,
                probabilities: array_map(
                    fn (array $probability) => DecisionOptionProbability::fromArray($probability),
                    $data['probabilities'],
                ),
            ),
            default => throw new \ValueError('Unknown decision answer kind.'),
        };
    }

    public function toArray(): array
    {
        return match ($this->kind) {
            'boolean' => [
                'kind' => 'boolean',
                'name' => $this->name,
                'probability' => $this->probability,
            ],
            'choice' => array_filter([
                'kind' => 'choice',
                'name' => $this->name,
                'value' => $this->value,
                'tied' => $this->tied,
                'confidence' => $this->confidence,
                'vendorConfidence' => $this->vendorConfidence,
                'probabilities' => $this->probabilities === null
                    ? null
                    : array_map(
                        fn (DecisionOptionProbability $probability) => $probability->toArray(),
                        $this->probabilities,
                    ),
            ], fn ($value) => $value !== null),
            default => throw new \ValueError('Unknown decision answer kind.'),
        };
    }
}
