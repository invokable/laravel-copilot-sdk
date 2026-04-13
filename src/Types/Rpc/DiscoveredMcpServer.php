<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ServerSource;

/**
 * Information about a discovered MCP server.
 */
readonly class DiscoveredMcpServer implements Arrayable
{
    /**
     * @param  string  $name  Server name (config key)
     * @param  ServerSource  $source  Configuration source
     * @param  bool  $enabled  Whether the server is enabled (not in the disabled list)
     * @param  ?string  $type  Server type: local, stdio, http, or sse
     */
    public function __construct(
        public string $name,
        public ServerSource $source,
        public bool $enabled,
        public ?string $type = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            source: ServerSource::from($data['source']),
            enabled: $data['enabled'],
            type: $data['type'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'source' => $this->source->value,
            'enabled' => $this->enabled,
            'type' => $this->type,
        ], fn ($v) => $v !== null);
    }
}
