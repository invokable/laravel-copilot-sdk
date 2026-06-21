<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpOauthPendingRequestResponseKind;

/**
 * Host response to a pending MCP OAuth request.
 */
readonly class McpOauthPendingRequestResponse implements Arrayable
{
    /**
     * @param  ?string  $accessToken  Access token acquired by the SDK host.
     * @param  ?int  $expiresIn  Token lifetime in seconds, if known.
     * @param  ?string  $refreshToken  Refresh token supplied by the host, if available.
     * @param  ?string  $tokenType  OAuth token type. Defaults to Bearer when omitted.
     */
    public function __construct(
        public McpOauthPendingRequestResponseKind|string $kind,
        public ?string $accessToken = null,
        public ?int $expiresIn = null,
        public ?string $refreshToken = null,
        public ?string $tokenType = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: McpOauthPendingRequestResponseKind::from($data['kind']),
            accessToken: $data['accessToken'] ?? null,
            expiresIn: $data['expiresIn'] ?? null,
            refreshToken: $data['refreshToken'] ?? null,
            tokenType: $data['tokenType'] ?? null,
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
            'expiresIn' => $this->expiresIn,
            'refreshToken' => $this->refreshToken,
            'tokenType' => $this->tokenType,
        ], fn ($v) => $v !== null);
    }
}
