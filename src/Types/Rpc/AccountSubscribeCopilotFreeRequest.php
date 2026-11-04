<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Identity to enroll in Copilot Free.
 */
readonly class AccountSubscribeCopilotFreeRequest implements Arrayable
{
    public function __construct(
        public AuthIdentity $identity,
    ) {}

    public static function fromArray(array $data): self
    {
        $identity = $data['identity'] ?? [];

        return new self(
            identity: $identity instanceof AuthIdentity ? $identity : AuthIdentity::fromArray($identity),
        );
    }

    public function toArray(): array
    {
        return [
            'identity' => $this->identity->toArray(),
        ];
    }
}
