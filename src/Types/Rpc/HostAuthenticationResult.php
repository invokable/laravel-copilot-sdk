<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Verified identity and expiration returned by a custom admission validator. */
readonly class HostAuthenticationResult implements Arrayable
{
    public function __construct(
        public string $identity,
        public string $expiresAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            identity: $data['identity'] ?? '',
            expiresAt: $data['expiresAt'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'identity' => $this->identity,
            'expiresAt' => $this->expiresAt,
        ];
    }
}
