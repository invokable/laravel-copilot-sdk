<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use SensitiveParameter;

/** Decrypted admission credential sent to the owning application's validator. */
readonly class HostValidateAuthenticationRequest implements Arrayable
{
    public string $token;

    public function __construct(
        public string $hostId,
        public string $requestId,
        #[SensitiveParameter] string $token,
    ) {
        $this->token = $token;
    }

    public static function fromArray(#[SensitiveParameter] array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            requestId: $data['requestId'] ?? '',
            token: $data['token'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'hostId' => $this->hostId,
            'requestId' => $this->requestId,
            'token' => $this->token,
        ];
    }
}
