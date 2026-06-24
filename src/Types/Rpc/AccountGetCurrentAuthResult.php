<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Current authentication state.
 */
readonly class AccountGetCurrentAuthResult implements Arrayable
{
    /**
     * @param  string[]|null  $authErrors  Authentication errors from the last auth attempt, if any.
     * @param  AuthInfo|null  $authInfo  Current authentication information, if authenticated.
     */
    public function __construct(
        public ?array $authErrors = null,
        public ?AuthInfo $authInfo = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            authErrors: $data['authErrors'] ?? null,
            authInfo: isset($data['authInfo']) ? AuthInfo::fromArray($data['authInfo']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'authErrors' => $this->authErrors,
            'authInfo' => $this->authInfo?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
