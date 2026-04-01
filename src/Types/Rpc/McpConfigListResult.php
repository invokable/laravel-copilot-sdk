<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of listing MCP server configurations.
 */
readonly class McpConfigListResult implements Arrayable
{
    /**
     * @param  array<string, McpServerValue>  $servers  All MCP servers from user config, keyed by name
     */
    public function __construct(
        public array $servers,
    ) {}

    public static function fromArray(array $data): self
    {
        $servers = [];
        foreach ($data['servers'] ?? [] as $name => $server) {
            $servers[$name] = McpServerValue::fromArray($server);
        }

        return new self(servers: $servers);
    }

    public function toArray(): array
    {
        return [
            'servers' => array_map(
                fn (McpServerValue $server) => $server->toArray(),
                $this->servers,
            ),
        ];
    }
}
