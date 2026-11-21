<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class AccountsLogoutRequest implements Arrayable
{
    public function __construct(public ?string $accountId = null, public ?string $selectionId = null) {}

    public static function fromArray(array $data): self
    {
        return new self(accountId: $data['accountId'] ?? null, selectionId: $data['selectionId'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter([
            'accountId' => $this->accountId,
            'selectionId' => $this->selectionId,
        ], static fn ($value): bool => $value !== null);
    }
}
