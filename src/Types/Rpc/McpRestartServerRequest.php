<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Server name and configuration for an individual MCP server restart.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpRestartServerRequest implements Arrayable
{
    /**
     * @param  string  $serverName  Name of the MCP server to restart
     * @param  array|null  $config  Optional server configuration
     */
    public function __construct(
        public string $serverName,
        public ?array $config = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
            config: $data['config'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'config' => $this->config,
        ], fn ($v) => $v !== null);
    }
}
