<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * MCP server to list app-callable tools for.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class MCPAppsListToolsRequest implements Arrayable
{
    /**
     * @param  string  $originServerName  **Required.** Server whose ui:// view issued the request. Per SEP-1865 ('callable by the app from this server only'), the call is rejected when this differs from `serverName`, and rejected outright when missing.
     * @param  string  $serverName  MCP server hosting the app.
     */
    public function __construct(
        public string $originServerName,
        public string $serverName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            originServerName: $data['originServerName'],
            serverName: $data['serverName'],
        );
    }

    public function toArray(): array
    {
        return [
            'originServerName' => $this->originServerName,
            'serverName' => $this->serverName,
        ];
    }
}
