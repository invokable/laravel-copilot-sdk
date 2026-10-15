<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of checking whether a remote MCP server's authorization server supports DCR.
 */
readonly class McpOauthCheckDcrSupportResult implements Arrayable
{
    public function __construct(
        public bool $supportsDcr,
        public ?string $authorizationServerUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            supportsDcr: (bool) ($data['supportsDcr'] ?? false),
            authorizationServerUrl: $data['authorizationServerUrl'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'supportsDcr' => $this->supportsDcr,
            'authorizationServerUrl' => $this->authorizationServerUrl,
        ], static fn ($value) => $value !== null);
    }
}
