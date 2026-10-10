<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request for the advertised live and dormant host session catalog. */
readonly class HostListSessionsRequest implements Arrayable
{
    public function __construct(
        public ?string $hostId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(hostId: $data['hostId'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter(['hostId' => $this->hostId], fn ($value) => $value !== null);
    }
}
