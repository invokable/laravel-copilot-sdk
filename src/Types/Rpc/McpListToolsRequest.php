<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Server name whose tool list should be returned.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpListToolsRequest implements Arrayable
{
    /**
     * @param  string  $serverName  Name of the connected MCP server whose tools to list.
     */
    public function __construct(
        public string $serverName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: $data['serverName'],
        );
    }

    public function toArray(): array
    {
        return [
            'serverName' => $this->serverName,
        ];
    }
}
