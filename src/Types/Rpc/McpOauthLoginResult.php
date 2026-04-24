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
     */
    public function __construct(
        public ?string $authorizationUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            authorizationUrl: $data['authorizationUrl'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'authorizationUrl' => $this->authorizationUrl,
        ], fn ($v) => $v !== null);
    }
}
