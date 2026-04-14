<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * An array field with anyOf items for UI elicitation forms (multi-select with anyOf).
 */
readonly class UIElicitationArrayAnyOfField implements Arrayable
{
    /**
     * @param  array{anyOf: array<array{const: string}>}  $items  Item schema with anyOf options
     * @param  ?string  $description  Human-readable field description
     * @param  ?int  $minItems  Minimum number of items
     * @param  ?int  $maxItems  Maximum number of items
     * @param  ?array<string>  $default  Default selected values
     */
    public function __construct(
        public array $items,
        public ?string $description = null,
        public ?int $minItems = null,
        public ?int $maxItems = null,
        public ?array $default = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            items: $data['items'],
            description: $data['description'] ?? null,
            minItems: $data['minItems'] ?? null,
            maxItems: $data['maxItems'] ?? null,
            default: $data['default'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => 'array',
            'items' => $this->items,
            'description' => $this->description,
            'minItems' => $this->minItems,
            'maxItems' => $this->maxItems,
            'default' => $this->default,
        ], fn ($v) => $v !== null);
    }
}
