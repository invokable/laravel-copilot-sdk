<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to classify an authorization server URL as Microsoft Entra ID.
 */
readonly class McpOauthIsMicrosoftAuthorizationServerRequest implements Arrayable
{
    public function __construct(public string $authorizationServerUrl) {}

    public static function fromArray(array $data): self
    {
        return new self(authorizationServerUrl: Arr::string($data, 'authorizationServerUrl'));
    }

    public function toArray(): array
    {
        return ['authorizationServerUrl' => $this->authorizationServerUrl];
    }
}
