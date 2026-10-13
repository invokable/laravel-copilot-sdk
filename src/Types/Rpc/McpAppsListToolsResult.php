<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * App-callable tools from the named MCP server.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpAppsListToolsResult implements Arrayable
{
    /**
     * @param  array<array<string, mixed>>  $tools  App-callable tools from the server.
     */
    public function __construct(
        public array $tools,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tools: Arr::array($data, 'tools'),
        );
    }

    public function toArray(): array
    {
        return [
            'tools' => $this->tools,
        ];
    }
}
