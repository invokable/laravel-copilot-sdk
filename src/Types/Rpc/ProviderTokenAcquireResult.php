<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A bearer token supplied by the SDK client for a BYOK provider.
 * The runtime sets it as `Authorization: Bearer <token>` on the outbound request and does no caching;
 * the SDK consumer owns token caching and refresh.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ProviderTokenAcquireResult implements Arrayable
{
    /**
     * @param  string  $token  The bearer token value (without the `Bearer ` prefix).
     */
    public function __construct(
        public string $token,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
