<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of classifying an authorization server URL as Microsoft Entra ID.
 */
readonly class McpOauthIsMicrosoftAuthorizationServerResult implements Arrayable
{
    public function __construct(public bool $isMicrosoftAuthorizationServer) {}

    public static function fromArray(array $data): self
    {
        return new self(isMicrosoftAuthorizationServer: (bool) ($data['isMicrosoftAuthorizationServer'] ?? false));
    }

    public function toArray(): array
    {
        return ['isMicrosoftAuthorizationServer' => $this->isMicrosoftAuthorizationServer];
    }
}
