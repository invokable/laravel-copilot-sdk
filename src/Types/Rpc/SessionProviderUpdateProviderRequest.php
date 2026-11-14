<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** @experimental */
readonly class SessionProviderUpdateProviderRequest implements Arrayable
{
    public function __construct(public SavedProviderConfigurationProviderInput $provider) {}

    public static function fromArray(array $data): self
    {
        $provider = $data['provider'] ?? [];

        return new self(provider: $provider instanceof SavedProviderConfigurationProviderInput
            ? $provider
            : SavedProviderConfigurationProviderInput::fromArray($provider));
    }

    public function toArray(): array
    {
        return ['provider' => $this->provider->toArray()];
    }
}
