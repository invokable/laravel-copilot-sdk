<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * User to log out.
 */
readonly class AccountLogoutRequest implements Arrayable
{
    /**
     * @param  AuthInfo  $authInfo  Authentication information for the user to log out.
     */
    public function __construct(
        public AuthInfo $authInfo,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            authInfo: AuthInfo::fromArray($data['authInfo'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'authInfo' => $this->authInfo->toArray(),
        ];
    }
}
