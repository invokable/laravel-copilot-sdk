<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * One question for a decision model to answer.
 *
 * @experimental
 */
readonly class DecisionQuestion implements Arrayable
{
    /**
     * @param  ?array<int, DecisionOption>  $options
     */
    public function __construct(
        public string $kind,
        public string $name,
        public string $instructions,
        public ?string $whenTrue = null,
        public ?string $whenFalse = null,
        public ?array $options = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return match ($data['kind']) {
            'boolean' => new self(
                kind: 'boolean',
                name: $data['name'],
                instructions: $data['instructions'],
                whenTrue: $data['whenTrue'] ?? null,
                whenFalse: $data['whenFalse'] ?? null,
            ),
            'choice' => new self(
                kind: 'choice',
                name: $data['name'],
                instructions: $data['instructions'],
                options: array_map(
                    fn (array $option) => DecisionOption::fromArray($option),
                    $data['options'],
                ),
            ),
            default => throw new \ValueError('Unknown decision question kind.'),
        };
    }

    public function toArray(): array
    {
        $fields = match ($this->kind) {
            'boolean' => [
                'whenTrue' => $this->whenTrue,
                'whenFalse' => $this->whenFalse,
            ],
            'choice' => [
                'options' => $this->options === null
                    ? null
                    : array_map(
                        fn (DecisionOption $option) => $option->toArray(),
                        $this->options,
                    ),
            ],
            default => throw new \ValueError('Unknown decision question kind.'),
        };

        return array_filter([
            'kind' => $this->kind,
            'name' => $this->name,
            'instructions' => $this->instructions,
            ...$fields,
        ], fn ($value) => $value !== null);
    }
}
