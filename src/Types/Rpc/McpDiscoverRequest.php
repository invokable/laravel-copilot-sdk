<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for discovering MCP servers.
 */
readonly class McpDiscoverRequest implements Arrayable
{
    /**
     * @param  ?string  $workingDirectory  Working directory used as context for discovery (e.g., plugin resolution)
     */
    public function __construct(
        public ?string $workingDirectory = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            workingDirectory: $data['workingDirectory'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'workingDirectory' => $this->workingDirectory,
        ], fn ($v) => $v !== null);
    }
}
