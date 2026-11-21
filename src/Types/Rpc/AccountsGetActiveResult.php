<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class AccountsGetActiveResult implements Arrayable
{
    public function __construct(
        public ?Account $account,
        public ?AuthIdentity $authInfo = null,
        public ?string $authSelectionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            account: isset($data['account']) ? Account::fromArray($data['account']) : null,
            authInfo: isset($data['authInfo']) ? AuthIdentity::fromArray($data['authInfo']) : null,
            authSelectionId: $data['authSelectionId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'account' => $this->account?->toArray(),
            'authInfo' => $this->authInfo?->toArray(),
            'authSelectionId' => $this->authSelectionId,
        ], static fn ($value, $key): bool => $key === 'account' || $value !== null, ARRAY_FILTER_USE_BOTH);
    }
}
