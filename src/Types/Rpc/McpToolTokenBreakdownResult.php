<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Per-server MCP tool definition token costs.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpToolTokenBreakdownResult implements Arrayable
{
    /**
     * @param  array<McpServerToolTokenBreakdown>  $servers
     */
    public function __construct(
        public array $servers,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            servers: array_map(
                fn (array $server) => McpServerToolTokenBreakdown::fromArray($server),
                $data['servers'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'servers' => array_map(fn (McpServerToolTokenBreakdown $server) => $server->toArray(), $this->servers),
        ];
    }
}
