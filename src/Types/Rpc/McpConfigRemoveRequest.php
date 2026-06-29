<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request for removing an MCP server configuration.
 */
readonly class McpConfigRemoveRequest implements Arrayable
{
    /**
     * @param  string  $name  Name of the MCP server to remove
     */
    public function __construct(
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
