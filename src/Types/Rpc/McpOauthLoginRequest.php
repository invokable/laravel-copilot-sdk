<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

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
     */
    public function __construct(
        public string $serverName,
        public ?string $callbackSuccessMessage = null,
        public ?string $clientName = null,
        public ?bool $forceReauth = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: $data['serverName'],
            callbackSuccessMessage: $data['callbackSuccessMessage'] ?? null,
            clientName: $data['clientName'] ?? null,
            forceReauth: $data['forceReauth'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'callbackSuccessMessage' => $this->callbackSuccessMessage,
            'clientName' => $this->clientName,
            'forceReauth' => $this->forceReauth,
        ], fn ($v) => $v !== null);
    }
}
