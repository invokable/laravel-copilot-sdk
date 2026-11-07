<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Inbound request for a Kerberos credential provider.
 *
 * @experimental
 */
readonly class KerberosAuthAcquireRequest implements Arrayable
{
    public function __construct(
        public string $spn,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            spn: Arr::string($data, 'spn'),
        );
    }

    public function toArray(): array
    {
        return [
            'spn' => $this->spn,
        ];
    }
}
