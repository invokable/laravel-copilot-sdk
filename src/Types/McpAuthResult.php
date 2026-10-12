<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpOauthPendingRequestResponseKind;

/**
 * Result returned by an MCP OAuth request handler.
 */
readonly class McpAuthResult implements Arrayable
{
    public function __construct(
        public McpOauthPendingRequestResponseKind|string $kind,
        public ?string $accessToken = null,
        public ?string $tokenType = null,
        public ?int $expiresIn = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $kind = $data['kind'] ?? McpOauthPendingRequestResponseKind::Cancelled->value;

        return new self(
            kind: is_string($kind)
                ? McpOauthPendingRequestResponseKind::tryFrom($kind) ?? $kind
                : McpOauthPendingRequestResponseKind::Cancelled,
            accessToken: $data['accessToken'] ?? null,
            tokenType: $data['tokenType'] ?? null,
            expiresIn: $data['expiresIn'] ?? null,
        );
    }

    public function toArray(): array
    {
        $kind = $this->kind instanceof McpOauthPendingRequestResponseKind
            ? $this->kind->value
            : $this->kind;

        return array_filter([
            'kind' => $kind,
            'accessToken' => $this->accessToken,
            'tokenType' => $this->tokenType,
            'expiresIn' => $this->expiresIn,
        ], fn ($value) => $value !== null);
    }
}
