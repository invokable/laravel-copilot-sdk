<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * One candidate answer to a choice question.
 *
 * @experimental
 */
readonly class DecisionOption implements Arrayable
{
    public function __construct(
        public string $value,
        public string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'],
            description: $data['description'],
        );
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'description' => $this->description,
        ];
    }
}
