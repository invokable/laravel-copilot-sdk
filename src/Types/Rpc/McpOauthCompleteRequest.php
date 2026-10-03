<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Callback delivered by a host-managed MCP OAuth redirect endpoint. */
readonly class McpOauthCompleteRequest implements Arrayable
{
    public function __construct(
        public string $authorizationId,
        public string $callbackUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            authorizationId: Arr::string($data, 'authorizationId', ''),
            callbackUrl: Arr::string($data, 'callbackUrl', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'authorizationId' => $this->authorizationId,
            'callbackUrl' => $this->callbackUrl,
        ];
    }
}
