<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * One page of resource templates advertised by the named MCP server.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpResourcesListTemplatesResult implements Arrayable
{
    /**
     * @param  McpResourceTemplate[]  $resourceTemplates  Resource templates advertised by the server
     * @param  string|null  $nextCursor  Opaque cursor for the next page, if the server has more resource templates
     */
    public function __construct(
        public array $resourceTemplates,
        public ?string $nextCursor = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            resourceTemplates: array_map(
                fn (array $r) => McpResourceTemplate::fromArray($r),
                $data['resourceTemplates'] ?? [],
            ),
            nextCursor: $data['nextCursor'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'resourceTemplates' => array_map(fn (McpResourceTemplate $r) => $r->toArray(), $this->resourceTemplates),
            'nextCursor' => $this->nextCursor,
        ], fn ($v) => $v !== null);
    }
}
