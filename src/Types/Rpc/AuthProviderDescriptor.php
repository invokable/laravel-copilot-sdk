<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\LoginProviderKind;

readonly class AuthProviderDescriptor implements Arrayable
{
    public function __construct(public LoginProviderKind|string $kind, public string $label, public bool $available) {}

    public static function fromArray(array $data): self
    {
        $kind = $data['kind'] ?? '';

        return new self(
            kind: LoginProviderKind::tryFrom($kind) ?? $kind,
            label: $data['label'] ?? '',
            available: (bool) ($data['available'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind instanceof LoginProviderKind ? $this->kind->value : $this->kind,
            'label' => $this->label,
            'available' => $this->available,
        ];
    }
}
