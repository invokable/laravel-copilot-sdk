<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Asks the SDK client to acquire a bearer token for a BYOK provider whose config set `hasBearerTokenProvider: true`.
 * Issued by the runtime before each outbound model request; the runtime does no caching, so this is sent once per request.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ProviderTokenAcquireRequest implements Arrayable
{
    /**
     * @param  string  $sessionId  Target session identifier.
     * @param  string  $providerName  Name of the BYOK provider needing a token.
     */
    public function __construct(
        public string $sessionId,
        public string $providerName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['sessionId'] ?? '',
            providerName: $data['providerName'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'providerName' => $this->providerName,
        ];
    }
}
