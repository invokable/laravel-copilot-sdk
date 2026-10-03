<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Session-scoped opaque account selection and its credential-free identity. */
readonly class ConnectorSessionAccount implements Arrayable
{
    public function __construct(
        public string $accountId,
        public AuthIdentityMetadata $authInfo,
    ) {}

    public static function fromArray(array $data): self
    {
        $authInfo = $data['authInfo'] ?? [];

        return new self(
            accountId: (string) ($data['accountId'] ?? ''),
            authInfo: $authInfo instanceof AuthIdentityMetadata ? $authInfo : AuthIdentityMetadata::fromArray($authInfo),
        );
    }

    public function toArray(): array
    {
        return [
            'accountId' => $this->accountId,
            'authInfo' => $this->authInfo->toArray(),
        ];
    }
}
