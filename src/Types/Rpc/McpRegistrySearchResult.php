<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Opaque server entries returned by the MCP registry.
 *
 * @experimental
 *
 * @internal Internal SDK API; not part of the public surface.
 */
readonly class McpRegistrySearchResult implements Arrayable
{
    /**
     * @param  mixed  $servers  Registry server data, preserved without interpreting its shape
     */
    public function __construct(
        public mixed $servers,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            servers: $data['servers'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'servers' => $this->servers,
        ];
    }
}
