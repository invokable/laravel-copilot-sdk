<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * MCP server and resource URI to fetch for an MCP App.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpAppsReadResourceRequest implements Arrayable
{
    /**
     * @param  string  $serverName  MCP server hosting the resource.
     * @param  string  $uri  Resource URI, typically ui://.
     */
    public function __construct(
        public string $serverName,
        public string $uri,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
            uri: Arr::string($data, 'uri'),
        );
    }

    public function toArray(): array
    {
        return [
            'serverName' => $this->serverName,
            'uri' => $this->uri,
        ];
    }
}
