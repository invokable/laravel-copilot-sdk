<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Kerberos credential returned to the runtime.
 *
 * @experimental
 */
readonly class KerberosAuthAcquireResult implements Arrayable
{
    public function __construct(
        public ?string $token = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            token: isset($data['token']) ? Arr::string($data, 'token') : null,
        );
    }

    public function toArray(): array
    {
        return $this->token === null ? [] : ['token' => $this->token];
    }
}
