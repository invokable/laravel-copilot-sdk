<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Request one page of prompts from an MCP server. */
readonly class McpPromptsListRequest implements Arrayable
{
    public function __construct(public string $serverName, public ?string $cursor = null) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName', ''),
            cursor: $data['cursor'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'cursor' => $this->cursor,
        ], fn ($value) => $value !== null);
    }
}
