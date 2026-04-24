<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to enable MCP servers globally (server-scoped config).
 */
readonly class McpConfigEnableRequest implements Arrayable
{
    /**
     * @param  array<string>  $names  Names of MCP servers to enable. Each server is removed from the persisted disabled list
     *                                so new sessions spawn it. Unknown or already-enabled names are ignored.
     */
    public function __construct(
        public array $names,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            names: $data['names'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'names' => $this->names,
        ];
    }
}
