<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpServerStatus;
use Revolution\Copilot\Enums\ServerSource;

/**
 * Information about an MCP server.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpServerInfo implements Arrayable
{
    /**
     * @param  string  $name  Server name (config key)
     * @param  McpServerStatus  $status  Connection status
     * @param  ServerSource|string|null  $source  Configuration source: user, workspace, plugin, or builtin
     * @param  ?string  $error  Error message if the server failed to connect
     */
    public function __construct(
        public string $name,
        public McpServerStatus $status,
        public ServerSource|string|null $source = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $source = $data['source'] ?? null;

        return new self(
            name: $data['name'],
            status: McpServerStatus::from($data['status']),
            source: $source !== null ? (ServerSource::tryFrom($source) ?? $source) : null,
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'status' => $this->status->value,
            'source' => $this->source instanceof ServerSource ? $this->source->value : $this->source,
            'error' => $this->error,
        ], fn ($v) => $v !== null);
    }
}
