<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Context shared by every question in a decisions request.
 *
 * @experimental
 */
readonly class DecisionContext implements Arrayable
{
    public function __construct(
        public string $kind,
        public ?string $text = null,
        public mixed $value = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return match ($data['kind']) {
            'text' => new self(kind: 'text', text: $data['text']),
            'json' => new self(kind: 'json', value: $data['value']),
            default => throw new \ValueError('Unknown decision context kind.'),
        };
    }

    public function toArray(): array
    {
        return match ($this->kind) {
            'text' => ['kind' => 'text', 'text' => $this->text],
            'json' => ['kind' => 'json', 'value' => $this->value],
            default => throw new \ValueError('Unknown decision context kind.'),
        };
    }
}
