<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\McpToolUiVisibility;

/**
 * Normalized MCP Apps discovery metadata from a tool's `_meta.ui` block.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpToolUi implements Arrayable
{
    /**
     * @param  ?string  $resourceUri  URI of the tool's MCP App resource, typically a `ui://` resource identifier. Use `session.mcp.resources.read` to fetch its HTML and resource metadata.
     * @param  ?array<McpToolUiVisibility>  $visibility  Tool visibility advertised by the server. When absent, MCP Apps defaults apply.
     */
    public function __construct(
        public ?string $resourceUri = null,
        public ?array $visibility = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            resourceUri: isset($data['resourceUri']) ? Arr::string($data, 'resourceUri') : null,
            visibility: isset($data['visibility'])
                ? array_map(fn (string $v) => McpToolUiVisibility::from($v), $data['visibility'])
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'resourceUri' => $this->resourceUri,
            'visibility' => $this->visibility !== null
                ? array_map(fn (McpToolUiVisibility $v) => $v->value, $this->visibility)
                : null,
        ], fn ($v) => $v !== null);
    }
}
