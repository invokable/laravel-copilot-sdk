<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for adding an MCP server configuration.
 */
readonly class McpConfigAddRequest implements Arrayable
{
    /**
     * @param  string  $name  Unique name for the MCP server
     * @param  McpServerValue|array  $config  MCP server configuration
     */
    public function __construct(
        public string $name,
        public McpServerValue|array $config,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            config: McpServerValue::fromArray($data['config'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'config' => $this->config instanceof McpServerValue
                ? $this->config->toArray()
                : $this->config,
        ];
    }
}
