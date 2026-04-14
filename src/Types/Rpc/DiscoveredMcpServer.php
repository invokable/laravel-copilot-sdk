<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpTransportType;
use Revolution\Copilot\Enums\ServerSource;

/**
 * Information about a discovered MCP server.
 */
readonly class DiscoveredMcpServer implements Arrayable
{
    /**
     * @param  string  $name  Server name (config key)
     * @param  ServerSource  $source  Configuration source
     * @param  bool  $enabled  Whether the server is enabled (not in the disabled list)
     * @param  McpTransportType|string|null  $type  Server transport type: stdio, http, sse, or memory (local configs are normalized to stdio)
     */
    public function __construct(
        public string $name,
        public ServerSource $source,
        public bool $enabled,
        public McpTransportType|string|null $type = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $type = $data['type'] ?? null;

        return new self(
            name: $data['name'],
            source: ServerSource::from($data['source']),
            enabled: $data['enabled'],
            type: $type !== null ? (McpTransportType::tryFrom($type) ?? $type) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'source' => $this->source->value,
            'enabled' => $this->enabled,
            'type' => $this->type instanceof McpTransportType ? $this->type->value : $this->type,
        ], fn ($v) => $v !== null);
    }
}
