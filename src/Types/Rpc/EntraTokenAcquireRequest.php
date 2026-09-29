<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\EntraTokenInteraction;

/** @experimental */
readonly class EntraTokenAcquireRequest implements Arrayable
{
    /**
     * @param  array<int, string>  $scopes
     */
    public function __construct(
        public string $clientId,
        public string $tenantId,
        public string $redirectUri,
        public array $scopes,
        public EntraTokenInteraction|string $interaction,
        public ?string $accessTokenToRenew = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'clientId' => $this->clientId,
            'tenantId' => $this->tenantId,
            'redirectUri' => $this->redirectUri,
            'scopes' => $this->scopes,
            'interaction' => $this->interaction instanceof EntraTokenInteraction
                ? $this->interaction->value
                : $this->interaction,
            'accessTokenToRenew' => $this->accessTokenToRenew,
        ], static fn ($value): bool => $value !== null);
    }
}
