<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to enable or disable remote-control steering.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsSetRemoteControlSteeringRequest implements Arrayable
{
    public function __construct(
        public bool $enabled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: Arr::boolean($data, 'enabled'),
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
        ];
    }
}
