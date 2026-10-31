<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of recording a queued-command wait.
 *
 * @internal
 */
readonly class CommandsReportQueuedCommandWaitResult implements Arrayable
{
    public function __construct(
        public bool $recorded,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            recorded: Arr::boolean($data, 'recorded', false),
        );
    }

    public function toArray(): array
    {
        return [
            'recorded' => $this->recorded,
        ];
    }
}
