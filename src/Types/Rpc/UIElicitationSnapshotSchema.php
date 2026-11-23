<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIElicitationSnapshotSchema implements Arrayable
{
    /**
     * @param  array<string, mixed>  $properties
     * @param  ?string[]  $required
     */
    public function __construct(
        public string $type,
        public array $properties,
        public ?array $required = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: Arr::string($data, 'type', ''),
            properties: Arr::array($data, 'properties', []),
            required: isset($data['required']) ? array_values(Arr::array($data, 'required')) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'properties' => $this->properties,
            'required' => $this->required,
        ], static fn ($value): bool => $value !== null);
    }
}
