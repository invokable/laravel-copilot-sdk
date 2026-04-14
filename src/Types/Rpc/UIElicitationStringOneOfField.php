<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A string field with oneOf options for UI elicitation forms.
 */
readonly class UIElicitationStringOneOfField implements Arrayable
{
    /**
     * @param  array<array{const: string}>  $oneOf  Available options with const values
     * @param  ?string  $description  Human-readable field description
     * @param  ?string  $default  Default selected value
     */
    public function __construct(
        public array $oneOf,
        public ?string $description = null,
        public ?string $default = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            oneOf: $data['oneOf'],
            description: $data['description'] ?? null,
            default: $data['default'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => 'string',
            'oneOf' => $this->oneOf,
            'description' => $this->description,
            'default' => $this->default,
        ], fn ($v) => $v !== null);
    }
}
