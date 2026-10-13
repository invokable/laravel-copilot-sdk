<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Runtime-issued handle for an MCP OAuth login.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionMcpOauthPrepareLoginResult implements Arrayable
{
    public function __construct(
        public string $loginId,
        public string $expiresAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            loginId: Arr::string($data, 'loginId'),
            expiresAt: Arr::string($data, 'expiresAt'),
        );
    }

    public function toArray(): array
    {
        return [
            'loginId' => $this->loginId,
            'expiresAt' => $this->expiresAt,
        ];
    }
}
