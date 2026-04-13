<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of MCP server discovery.
 */
readonly class McpDiscoverResult implements Arrayable
{
    /**
     * @param  array<int, DiscoveredMcpServer>  $servers  MCP servers discovered from all sources
     */
    public function __construct(
        public array $servers,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            servers: array_map(
                fn (array $server) => DiscoveredMcpServer::fromArray($server),
                $data['servers'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'servers' => array_map(
                fn (DiscoveredMcpServer $server) => $server->toArray(),
                $this->servers,
            ),
        ];
    }
}
