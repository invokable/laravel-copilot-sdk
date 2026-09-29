<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\LoginProviderKind;

readonly class ProviderDescriptor implements Arrayable
{
    public function __construct(public LoginProviderKind|string $kind, public string $label, public bool $available) {}

    public static function fromArray(array $data): self
    {
        return new self(LoginProviderKind::tryFrom($data['kind'] ?? '') ?? ($data['kind'] ?? ''), $data['label'] ?? '', (bool) ($data['available'] ?? false));
    }

    public function toArray(): array
    {
        return ['kind' => $this->kind instanceof LoginProviderKind ? $this->kind->value : $this->kind, 'label' => $this->label, 'available' => $this->available];
    }
}
