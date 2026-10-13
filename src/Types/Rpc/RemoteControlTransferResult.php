<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of transferring runtime-managed remote control.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RemoteControlTransferResult implements Arrayable
{
    public function __construct(
        public RemoteControlStatus|array $status,
        public bool $transferred,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = $data['status'] ?? [];

        return new self(
            status: $status instanceof RemoteControlStatus
                ? $status
                : RemoteControlStatus::fromArray($status),
            transferred: Arr::boolean($data, 'transferred'),
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status instanceof RemoteControlStatus
                ? $this->status->toArray()
                : $this->status,
            'transferred' => $this->transferred,
        ];
    }
}
