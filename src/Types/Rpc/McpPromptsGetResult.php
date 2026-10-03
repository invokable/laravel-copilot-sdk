<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Rendered prompt messages returned by an MCP server. */
readonly class McpPromptsGetResult implements Arrayable
{
    /** @param array<McpPromptMessage|array> $messages */
    public function __construct(
        public array $messages = [],
        public ?string $description = null,
        public ?array $_meta = null,
        public ?array $additionalProperties = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messages: array_map(
                fn ($item) => $item instanceof McpPromptMessage ? $item : McpPromptMessage::fromArray($item),
                $data['messages'] ?? [],
            ),
            description: $data['description'] ?? null,
            _meta: $data['_meta'] ?? null,
            additionalProperties: $data['additionalProperties'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'messages' => array_map(
                fn ($item) => $item instanceof McpPromptMessage ? $item->toArray() : $item,
                $this->messages,
            ),
            'description' => $this->description,
            '_meta' => $this->_meta,
            'additionalProperties' => $this->additionalProperties,
        ], fn ($value) => $value !== null);
    }
}
