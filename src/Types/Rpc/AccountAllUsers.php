<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * List of all authenticated users.
 */
readonly class AccountAllUsers implements Arrayable
{
    /**
     * @param  AuthInfo  $authInfo  Authentication information for this user.
     * @param  ?string  $token  Associated token, if available.
     */
    public function __construct(
        public AuthInfo $authInfo,
        public ?string $token = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            authInfo: AuthInfo::fromArray($data['authInfo'] ?? []),
            token: $data['token'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'authInfo' => $this->authInfo->toArray(),
            'token' => $this->token,
        ], fn ($v) => $v !== null);
    }
}
