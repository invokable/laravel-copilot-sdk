<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to stop runtime-managed remote control.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsStopRemoteControlRequest implements Arrayable
{
    public function __construct(
        public ?string $expectedSessionId = null,
        public ?bool $force = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            expectedSessionId: $data['expectedSessionId'] ?? null,
            force: isset($data['force']) ? Arr::boolean($data, 'force') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'expectedSessionId' => $this->expectedSessionId,
            'force' => $this->force,
        ], static fn ($value) => $value !== null);
    }
}
