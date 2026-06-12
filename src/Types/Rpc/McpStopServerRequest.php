<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Server name for an individual MCP server stop.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpStopServerRequest implements Arrayable
{
    /**
     * @param  string  $serverName  Name of the MCP server to stop.
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
