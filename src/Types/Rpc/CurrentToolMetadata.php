<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Lightweight metadata for a currently initialized session tool.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CurrentToolMetadata implements Arrayable
{
    /**
     * @param  string  $name  Model-facing tool name.
     * @param  string  $description  Tool description.
     * @param  ?string  $namespacedName  Optional MCP/config namespaced tool name.
     * @param  ?string  $mcpServerName  MCP server name for MCP-backed tools.
     * @param  ?string  $mcpToolName  Raw MCP tool name for MCP-backed tools.
     * @param  ?array  $inputSchema  JSON Schema for tool input.
     * @param  ?bool  $deferLoading  Whether the tool is loaded on demand via tool search.
     */
    public function __construct(
        public string $name,
        public string $description,
        public ?string $namespacedName = null,
        public ?string $mcpServerName = null,
        public ?string $mcpToolName = null,
        public ?array $inputSchema = null,
        public ?bool $deferLoading = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            description: $data['description'] ?? '',
            namespacedName: $data['namespacedName'] ?? null,
            mcpServerName: $data['mcpServerName'] ?? null,
            mcpToolName: $data['mcpToolName'] ?? null,
            inputSchema: $data['input_schema'] ?? null,
            deferLoading: $data['deferLoading'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'namespacedName' => $this->namespacedName,
            'mcpServerName' => $this->mcpServerName,
            'mcpToolName' => $this->mcpToolName,
            'input_schema' => $this->inputSchema,
            'deferLoading' => $this->deferLoading,
        ], fn ($v) => $v !== null);
    }
}
