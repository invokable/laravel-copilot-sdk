<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Tools exposed by the connected MCP server. Throws when the server is not connected.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpListToolsResult implements Arrayable
{
    /**
     * @param  McpTools[]  $tools  Tools exposed by the server.
     */
    public function __construct(
        public array $tools,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tools: array_map(
                fn (array $tool) => McpTools::fromArray($tool),
                $data['tools'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'tools' => array_map(fn (McpTools $tool) => $tool->toArray(), $this->tools),
        ];
    }
}
