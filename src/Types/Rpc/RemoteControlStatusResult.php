<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result containing the current runtime-managed remote-control status.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RemoteControlStatusResult implements Arrayable
{
    public function __construct(
        public RemoteControlStatus|array $status,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = $data['status'] ?? [];

        return new self(
            status: $status instanceof RemoteControlStatus
                ? $status
                : RemoteControlStatus::fromArray($status),
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status instanceof RemoteControlStatus
                ? $this->status->toArray()
                : $this->status,
        ];
    }
}
