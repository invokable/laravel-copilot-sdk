<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Context tokens one MCP server's tool definitions cost.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpServerToolTokenBreakdown implements Arrayable
{
    /**
     * @param  array<McpToolTokenCount>  $tools
     */
    public function __construct(
        public string $serverName,
        public int $totalTokens,
        public array $tools,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
            totalTokens: Arr::integer($data, 'totalTokens'),
            tools: array_map(
                fn (array $tool) => McpToolTokenCount::fromArray($tool),
                $data['tools'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'serverName' => $this->serverName,
            'totalTokens' => $this->totalTokens,
            'tools' => array_map(fn (McpToolTokenCount $tool) => $tool->toArray(), $this->tools),
        ];
    }
}
