<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\LoginProviderKind;

readonly class AuthLoginListRequest implements Arrayable
{
    public function __construct(public LoginProviderKind|string $provider) {}

    public static function fromArray(array $data): self
    {
        $provider = $data['provider'] ?? '';

        return new self(provider: LoginProviderKind::tryFrom($provider) ?? $provider);
    }

    public function toArray(): array
    {
        return ['provider' => $this->provider instanceof LoginProviderKind ? $this->provider->value : $this->provider];
    }
}
