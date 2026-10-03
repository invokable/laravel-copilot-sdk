<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** One page of prompts advertised by an MCP server. */
readonly class McpPromptsListResult implements Arrayable
{
    /** @param array<McpPrompt|array> $prompts */
    public function __construct(
        public array $prompts = [],
        public ?string $nextCursor = null,
        public ?array $_meta = null,
        public ?array $additionalProperties = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            prompts: array_map(
                fn ($item) => $item instanceof McpPrompt ? $item : McpPrompt::fromArray($item),
                $data['prompts'] ?? [],
            ),
            nextCursor: $data['nextCursor'] ?? null,
            _meta: $data['_meta'] ?? null,
            additionalProperties: $data['additionalProperties'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'prompts' => array_map(
                fn ($item) => $item instanceof McpPrompt ? $item->toArray() : $item,
                $this->prompts,
            ),
            'nextCursor' => $this->nextCursor,
            '_meta' => $this->_meta,
            'additionalProperties' => $this->additionalProperties,
        ], fn ($value) => $value !== null);
    }
}
