<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to dispose a supervised host listener. */
readonly class HostDisposeRequest implements Arrayable
{
    public function __construct(
        public string $hostId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'hostId' => $this->hostId,
        ];
    }
}
