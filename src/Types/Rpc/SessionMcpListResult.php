<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of listing MCP servers.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionMcpListResult implements Arrayable
{
    /**
     * @param  array<McpServerInfo>  $servers  Configured MCP servers
     */
    public function __construct(
        public array $servers,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            servers: array_map(
                fn (array $server) => McpServerInfo::fromArray($server),
                $data['servers'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'servers' => array_map(fn (McpServerInfo $server) => $server->toArray(), $this->servers),
        ];
    }
}
