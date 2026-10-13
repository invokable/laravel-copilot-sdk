<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to transfer runtime-managed remote control to another session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsTransferRemoteControlRequest implements Arrayable
{
    public function __construct(
        public string $toSessionId,
        public ?string $expectedFromSessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            toSessionId: Arr::string($data, 'toSessionId'),
            expectedFromSessionId: $data['expectedFromSessionId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'toSessionId' => $this->toSessionId,
            'expectedFromSessionId' => $this->expectedFromSessionId,
        ], static fn ($value) => $value !== null);
    }
}
