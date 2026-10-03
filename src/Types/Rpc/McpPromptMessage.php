<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** MCP prompt message preserving the original content block. */
readonly class McpPromptMessage implements Arrayable
{
    public function __construct(
        public string $role,
        public mixed $content,
        public ?array $_meta = null,
        public ?array $additionalProperties = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            role: Arr::string($data, 'role', ''),
            content: $data['content'] ?? null,
            _meta: $data['_meta'] ?? null,
            additionalProperties: $data['additionalProperties'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'role' => $this->role,
            'content' => $this->content,
            '_meta' => $this->_meta,
            'additionalProperties' => $this->additionalProperties,
        ], fn ($value) => $value !== null);
    }
}
