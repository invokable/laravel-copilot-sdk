<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A string field with a fixed set of enum values for UI elicitation forms.
 */
readonly class UIElicitationStringEnumField implements Arrayable
{
    /**
     * @param  array<string>  $enum  Allowed string values
     * @param  ?string  $description  Human-readable field description
     * @param  ?array<string>  $enumNames  Optional display names for enum values
     * @param  ?string  $default  Default selected value
     */
    public function __construct(
        public array $enum,
        public ?string $description = null,
        public ?array $enumNames = null,
        public ?string $default = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enum: $data['enum'],
            description: $data['description'] ?? null,
            enumNames: $data['enumNames'] ?? null,
            default: $data['default'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => 'string',
            'enum' => $this->enum,
            'description' => $this->description,
            'enumNames' => $this->enumNames,
            'default' => $this->default,
        ], fn ($v) => $v !== null);
    }
}
