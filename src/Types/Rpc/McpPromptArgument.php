<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Argument accepted by an MCP prompt. */
readonly class McpPromptArgument implements Arrayable
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?bool $required = null,
        public ?array $_meta = null,
        public ?array $additionalProperties = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name', ''),
            description: $data['description'] ?? null,
            required: $data['required'] ?? null,
            _meta: $data['_meta'] ?? null,
            additionalProperties: $data['additionalProperties'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'required' => $this->required,
            '_meta' => $this->_meta,
            'additionalProperties' => $this->additionalProperties,
        ], fn ($value) => $value !== null);
    }
}
