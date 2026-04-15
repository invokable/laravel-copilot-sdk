<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for enabling an MCP server.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpEnableRequest implements Arrayable
{
    /**
     * @param  string  $serverName  Name of the MCP server to enable
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
