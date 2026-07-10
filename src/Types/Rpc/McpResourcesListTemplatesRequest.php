<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * MCP server whose resource templates to enumerate.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpResourcesListTemplatesRequest implements Arrayable
{
    /**
     * @param  string  $serverName  Name of the MCP server whose resource templates to enumerate
     * @param  string|null  $cursor  Opaque MCP pagination cursor from a prior `nextCursor` value
     */
    public function __construct(
        public string $serverName,
        public ?string $cursor = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
            cursor: isset($data['cursor']) ? Arr::string($data, 'cursor') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'cursor' => $this->cursor,
        ], fn ($v) => $v !== null);
    }
}
