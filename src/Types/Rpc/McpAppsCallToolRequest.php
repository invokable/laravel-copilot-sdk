<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * MCP server, tool name, and arguments to invoke from an MCP App view.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpAppsCallToolRequest implements Arrayable
{
    /**
     * @param  string  $serverName  MCP server hosting the tool.
     * @param  string  $toolName  MCP tool name.
     * @param  string  $originServerName  **Required.** Server whose ui:// view issued the request.
     * @param  array<string, mixed>|null  $arguments  Tool arguments.
     */
    public function __construct(
        public string $serverName,
        public string $toolName,
        public string $originServerName,
        public ?array $arguments = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
            toolName: Arr::string($data, 'toolName'),
            originServerName: Arr::string($data, 'originServerName'),
            arguments: isset($data['arguments']) ? Arr::array($data, 'arguments') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'toolName' => $this->toolName,
            'arguments' => $this->arguments,
            'originServerName' => $this->originServerName,
        ], fn ($value) => $value !== null);
    }
}
