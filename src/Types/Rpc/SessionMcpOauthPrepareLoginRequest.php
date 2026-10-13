<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Effect-free preparation for an owned MCP OAuth login.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionMcpOauthPrepareLoginRequest implements Arrayable
{
    public function __construct(
        public string $serverName,
        public string $expectedInstallationId,
        public ?bool $forceReauth = null,
        public ?string $clientName = null,
        public ?string $callbackSuccessMessage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
            expectedInstallationId: Arr::string($data, 'expectedInstallationId'),
            forceReauth: $data['forceReauth'] ?? null,
            clientName: $data['clientName'] ?? null,
            callbackSuccessMessage: $data['callbackSuccessMessage'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'expectedInstallationId' => $this->expectedInstallationId,
            'forceReauth' => $this->forceReauth,
            'clientName' => $this->clientName,
            'callbackSuccessMessage' => $this->callbackSuccessMessage,
        ], fn ($value) => $value !== null);
    }
}
