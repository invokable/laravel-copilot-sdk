<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Identifies an MCP server whose persisted OAuth credentials were updated.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpOauthAuthenticationStateChangedRequest implements Arrayable
{
    public function __construct(
        public ?string $serverName = null,
        public ?bool $refreshSessionToken = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: $data['serverName'] ?? null,
            refreshSessionToken: $data['refreshSessionToken'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'refreshSessionToken' => $this->refreshSessionToken,
        ], fn ($value): bool => $value !== null);
    }
}
