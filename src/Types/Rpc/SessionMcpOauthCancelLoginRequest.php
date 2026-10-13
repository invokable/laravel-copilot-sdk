<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to cancel an owned MCP OAuth login.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionMcpOauthCancelLoginRequest implements Arrayable
{
    public function __construct(
        public string $expectedInstallationId,
        public string $loginId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            expectedInstallationId: Arr::string($data, 'expectedInstallationId'),
            loginId: Arr::string($data, 'loginId'),
        );
    }

    public function toArray(): array
    {
        return [
            'expectedInstallationId' => $this->expectedInstallationId,
            'loginId' => $this->loginId,
        ];
    }
}
