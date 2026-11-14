<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SavedProviderCredentialStatus;

/** @experimental */
readonly class SavedProviderConfigurationProvider implements Arrayable
{
    public function __construct(
        public SavedProviderConfigurationProviderInput $provider,
        public SavedProviderCredentialStatus|string $credentialStatus,
    ) {}

    public static function fromArray(array $data): self
    {
        $provider = $data['provider'] ?? [];
        $status = $data['credentialStatus'] ?? '';

        return new self(
            provider: $provider instanceof SavedProviderConfigurationProviderInput
                ? $provider
                : SavedProviderConfigurationProviderInput::fromArray($provider),
            credentialStatus: is_string($status) ? (SavedProviderCredentialStatus::tryFrom($status) ?? $status) : $status,
        );
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider->toArray(),
            'credentialStatus' => $this->credentialStatus instanceof SavedProviderCredentialStatus
                ? $this->credentialStatus->value
                : $this->credentialStatus,
        ];
    }
}
