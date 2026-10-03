<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of initiating MCP OAuth login.
 */
readonly class McpOauthLoginResult implements Arrayable
{
    /**
     * @param  ?string  $authorizationUrl  URL the caller should open in a browser to complete OAuth.
     *                                     Omitted when cached tokens were still valid and no browser interaction was needed.
     * @param  ?string  $authorizationId  Opaque authorization identifier for host-managed redirect callbacks.
     */
    public function __construct(
        public ?string $authorizationUrl = null,
        public ?string $authorizationId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            authorizationUrl: $data['authorizationUrl'] ?? null,
            authorizationId: $data['authorizationId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'authorizationUrl' => $this->authorizationUrl,
            'authorizationId' => $this->authorizationId,
        ], fn ($v) => $v !== null);
    }
}
