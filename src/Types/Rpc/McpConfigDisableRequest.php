<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to disable MCP servers globally (server-scoped config).
 */
readonly class McpConfigDisableRequest implements Arrayable
{
    /**
     * @param  array<string>  $names  Names of MCP servers to disable. Each server is added to the persisted disabled list so
     *                                new sessions skip it. Already-disabled names are ignored. Active sessions keep their
     *                                current connections until they end.
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
