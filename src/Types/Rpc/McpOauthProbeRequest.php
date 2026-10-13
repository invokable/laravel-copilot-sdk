<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to passively probe OAuth for a configured remote MCP server.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpOauthProbeRequest implements Arrayable
{
    public function __construct(
        public string $serverName,
        public ?string $expectedInstallationId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
            expectedInstallationId: $data['expectedInstallationId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'expectedInstallationId' => $this->expectedInstallationId,
        ], fn ($value) => $value !== null);
    }
}
