<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to reload session MCP connections with an explicit configuration.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpReloadWithConfigRequest implements Arrayable
{
    public function __construct(public McpReloadConfig|array $config) {}

    public static function fromArray(array $data): self
    {
        $config = $data['config'] ?? [];

        return new self(
            config: $config instanceof McpReloadConfig ? $config : McpReloadConfig::fromArray($config),
        );
    }

    public function toArray(): array
    {
        return [
            'config' => $this->config instanceof McpReloadConfig ? $this->config->toArray() : $this->config,
        ];
    }
}
