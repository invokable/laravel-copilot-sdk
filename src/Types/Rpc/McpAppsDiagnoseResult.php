<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Diagnostic snapshot of MCP Apps wiring for the named server.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpAppsDiagnoseResult implements Arrayable
{
    public function __construct(
        public MCPAppsDiagnoseCapability $capability,
        public MCPAppsDiagnoseServer $server,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            capability: MCPAppsDiagnoseCapability::fromArray(Arr::array($data, 'capability')),
            server: MCPAppsDiagnoseServer::fromArray(Arr::array($data, 'server')),
        );
    }

    public function toArray(): array
    {
        return [
            'capability' => $this->capability->toArray(),
            'server' => $this->server->toArray(),
        ];
    }
}
