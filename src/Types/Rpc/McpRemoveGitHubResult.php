<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether the auto-managed GitHub MCP server was removed.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpRemoveGitHubResult implements Arrayable
{
    public function __construct(
        public bool $removed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            removed: Arr::boolean($data, 'removed'),
        );
    }

    public function toArray(): array
    {
        return [
            'removed' => $this->removed,
        ];
    }
}
