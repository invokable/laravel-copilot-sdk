<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * MCP server to diagnose MCP Apps wiring for.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class MCPAppsDiagnoseRequest implements Arrayable
{
    /**
     * @param  string  $serverName  MCP server to probe.
     */
    public function __construct(
        public string $serverName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
        );
    }

    public function toArray(): array
    {
        return [
            'serverName' => $this->serverName,
        ];
    }
}
