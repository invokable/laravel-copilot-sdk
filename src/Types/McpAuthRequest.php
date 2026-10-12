<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * MCP OAuth request that the host can satisfy with a host-acquired token.
 */
readonly class McpAuthRequest implements Arrayable
{
    /**
     * @param  'initial'|'refresh'|'reauth'|'upscope'  $reason
     * @param  array{resourceMetadataUrl?: string, scope?: string, error?: string}|null  $wwwAuthenticateParams
     * @param  array{clientId: string, clientSecret?: string, grantType?: 'client_credentials', publicClient?: bool, scope?: string}|null  $staticClientConfig
     */
    public function __construct(
        public string $requestId,
        public string $serverName,
        public string $serverUrl,
        public string $reason,
        public ?array $wwwAuthenticateParams = null,
        public ?string $resourceMetadata = null,
        public ?array $staticClientConfig = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId', ''),
            serverName: Arr::string($data, 'serverName', ''),
            serverUrl: Arr::string($data, 'serverUrl', ''),
            reason: Arr::string($data, 'reason', ''),
            wwwAuthenticateParams: is_array($data['wwwAuthenticateParams'] ?? null) ? $data['wwwAuthenticateParams'] : null,
            resourceMetadata: $data['resourceMetadata'] ?? null,
            staticClientConfig: is_array($data['staticClientConfig'] ?? null) ? $data['staticClientConfig'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'serverName' => $this->serverName,
            'serverUrl' => $this->serverUrl,
            'reason' => $this->reason,
            'wwwAuthenticateParams' => $this->wwwAuthenticateParams,
            'resourceMetadata' => $this->resourceMetadata,
            'staticClientConfig' => $this->staticClientConfig,
        ], fn ($value) => $value !== null);
    }
}
