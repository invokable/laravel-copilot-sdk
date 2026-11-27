<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class McpDiscoverPluginServersResult implements Arrayable
{
    /**
     * @param  array<string, array<string, mixed>>  $mcpServers
     * @param  ?array<int, string>  $disabledServers
     * @param  ?array<int, string>  $enabledServers
     */
    public function __construct(
        public array $mcpServers,
        public ?array $disabledServers = null,
        public ?array $enabledServers = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mcpServers: Arr::array($data, 'mcpServers'),
            disabledServers: isset($data['disabledServers']) ? Arr::array($data, 'disabledServers') : null,
            enabledServers: isset($data['enabledServers']) ? Arr::array($data, 'enabledServers') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mcpServers' => $this->mcpServers,
            'disabledServers' => $this->disabledServers,
            'enabledServers' => $this->enabledServers,
        ], fn ($value): bool => $value !== null);
    }
}
