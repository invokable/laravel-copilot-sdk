<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Groups related tools for model tool discovery.
 */
readonly class ToolNamespace implements Arrayable
{
    /**
     * @param  string  $name  Namespace name (1-64 ASCII letters, digits, underscores, or hyphens)
     * @param  ?string  $description  Optional model-visible explanation of the tools in this namespace
     */
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}

    /**
     * Create from array.
     *
     * @param  array{name: string, description?: ?string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            description: $data['description'] ?? null,
        );
    }

    /**
     * Convert to array.
     *
     * @return array{name: string, description?: string}
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
        ], static fn ($value) => $value !== null);
    }
}
