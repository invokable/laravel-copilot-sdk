<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Schema for a single MCP tool entry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpTools implements Arrayable
{
    /**
     * @param  string  $name  Tool name.
     * @param  ?string  $description  Tool description, when provided.
     */
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
        ], fn ($v) => $v !== null);
    }
}
