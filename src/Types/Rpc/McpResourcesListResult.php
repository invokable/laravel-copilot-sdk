<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * One page of resources advertised by the named MCP server.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpResourcesListResult implements Arrayable
{
    /**
     * @param  McpResource[]  $resources   Resources advertised by the server
     * @param  string|null  $nextCursor  Opaque cursor for the next page, if the server has more resources
     */
    public function __construct(
        public array $resources,
        public ?string $nextCursor = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            resources: array_map(
                fn (array $r) => McpResource::fromArray($r),
                $data['resources'] ?? [],
            ),
            nextCursor: $data['nextCursor'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'resources' => array_map(fn (McpResource $r) => $r->toArray(), $this->resources),
            'nextCursor' => $this->nextCursor,
        ], fn ($v) => $v !== null);
    }
}
