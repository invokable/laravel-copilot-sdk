<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to check whether a remote MCP server's authorization server supports DCR.
 */
readonly class McpOauthCheckDcrSupportRequest implements Arrayable
{
    public function __construct(
        public string $serverUrl,
        public ?string $resourceMetadataText = null,
        public ?string $sandboxProxy = null,
        public ?string $sessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverUrl: Arr::string($data, 'serverUrl'),
            resourceMetadataText: $data['resourceMetadataText'] ?? null,
            sandboxProxy: $data['sandboxProxy'] ?? null,
            sessionId: $data['sessionId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverUrl' => $this->serverUrl,
            'resourceMetadataText' => $this->resourceMetadataText,
            'sandboxProxy' => $this->sandboxProxy,
            'sessionId' => $this->sessionId,
        ], static fn ($value) => $value !== null);
    }
}
