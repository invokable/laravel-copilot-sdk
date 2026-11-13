<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use SensitiveParameter;

/** @experimental */
readonly class ProvidersConfigUpdateProviderRequest implements Arrayable
{
    public function __construct(
        public SavedProviderConfigurationProviderInput $provider,
        #[SensitiveParameter]
        public ?string $credential = null,
    ) {}

    public static function fromArray(#[SensitiveParameter] array $data): self
    {
        $provider = $data['provider'] ?? [];

        return new self(
            provider: $provider instanceof SavedProviderConfigurationProviderInput
                ? $provider
                : SavedProviderConfigurationProviderInput::fromArray($provider),
            credential: $data['credential'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'provider' => $this->provider->toArray(),
            'credential' => $this->credential,
        ], fn ($value) => $value !== null);
    }
}
