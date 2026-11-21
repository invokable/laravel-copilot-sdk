<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class AccountsListRequest implements Arrayable
{
    public function __construct(public ?bool $refresh = null) {}

    public static function fromArray(array $data): self
    {
        return new self(refresh: $data['refresh'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter(['refresh' => $this->refresh], static fn ($value): bool => $value !== null);
    }
}
