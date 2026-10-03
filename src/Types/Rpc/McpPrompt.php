<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Prompt descriptor advertised by an MCP server. */
readonly class McpPrompt implements Arrayable
{
    /** @param array<McpPromptArgument|array> $arguments @param array<McpPromptIcon|array> $icons */
    public function __construct(
        public string $name,
        public ?string $title = null,
        public ?string $description = null,
        public ?array $arguments = null,
        public ?array $icons = null,
        public ?array $_meta = null,
        public ?array $additionalProperties = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name', ''),
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            arguments: isset($data['arguments']) ? array_map(
                fn ($item) => $item instanceof McpPromptArgument ? $item : McpPromptArgument::fromArray($item),
                $data['arguments'],
            ) : null,
            icons: isset($data['icons']) ? array_map(
                fn ($item) => $item instanceof McpPromptIcon ? $item : McpPromptIcon::fromArray($item),
                $data['icons'],
            ) : null,
            _meta: $data['_meta'] ?? null,
            additionalProperties: $data['additionalProperties'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'arguments' => $this->arguments === null ? null : array_map(
                fn ($item) => $item instanceof McpPromptArgument ? $item->toArray() : $item,
                $this->arguments,
            ),
            'icons' => $this->icons === null ? null : array_map(
                fn ($item) => $item instanceof McpPromptIcon ? $item->toArray() : $item,
                $this->icons,
            ),
            '_meta' => $this->_meta,
            'additionalProperties' => $this->additionalProperties,
        ], fn ($value) => $value !== null);
    }
}
