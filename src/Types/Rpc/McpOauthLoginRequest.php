<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\McpOauthLoginGrantType;

/**
 * Request to initiate MCP OAuth login for a remote server.
 */
readonly class McpOauthLoginRequest implements Arrayable
{
    /**
     * @param  string  $serverName  Name of the remote MCP server to authenticate.
     * @param  ?string  $callbackSuccessMessage  Optional override for the body text shown on the OAuth loopback callback success page.
     * @param  ?string  $clientName  Optional override for the OAuth client display name shown on the consent screen.
     * @param  ?bool  $forceReauth  When true, clears any cached OAuth token for the server and runs a full new authorization.
     * @param  ?string  $clientId  Optional OAuth client ID override. When set, uses this pre-registered static client instead of dynamic client registration.
     * @param  ?string  $clientSecret  Optional OAuth client secret override. Treated as an ephemeral host-owned secret; not persisted.
     * @param  ?bool  $publicClient  Optional override indicating whether the static OAuth client is public.
     * @param  McpOauthLoginGrantType|string|null  $grantType  Optional OAuth grant type override for this login.
     */
    public function __construct(
        public string $serverName,
        public ?string $callbackSuccessMessage = null,
        public ?string $clientName = null,
        public ?bool $forceReauth = null,
        public ?string $clientId = null,
        public ?string $clientSecret = null,
        public ?bool $publicClient = null,
        public McpOauthLoginGrantType|string|null $grantType = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $grantType = isset($data['grantType'])
            ? (McpOauthLoginGrantType::tryFrom($data['grantType']) ?? $data['grantType'])
            : null;

        return new self(
            serverName: Arr::string($data, 'serverName'),
            callbackSuccessMessage: $data['callbackSuccessMessage'] ?? null,
            clientName: $data['clientName'] ?? null,
            forceReauth: $data['forceReauth'] ?? null,
            clientId: $data['clientId'] ?? null,
            clientSecret: $data['clientSecret'] ?? null,
            publicClient: $data['publicClient'] ?? null,
            grantType: $grantType,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'callbackSuccessMessage' => $this->callbackSuccessMessage,
            'clientName' => $this->clientName,
            'forceReauth' => $this->forceReauth,
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'publicClient' => $this->publicClient,
            'grantType' => $this->grantType instanceof McpOauthLoginGrantType ? $this->grantType->value : $this->grantType,
        ], fn ($v) => $v !== null);
    }
}
