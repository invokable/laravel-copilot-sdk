<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Whether GitHub MCP tools can be replaced by the host's GitHub CLI.
 *
 * @experimental
 *
 * @internal Internal SDK API; not part of the public surface.
 */
readonly class McpShouldExcludeGitHubToolsResult implements Arrayable
{
    public function __construct(
        public bool $excludeGhReplaceableTools,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            excludeGhReplaceableTools: Arr::boolean($data, 'excludeGhReplaceableTools', false),
        );
    }

    public function toArray(): array
    {
        return [
            'excludeGhReplaceableTools' => $this->excludeGhReplaceableTools,
        ];
    }
}
