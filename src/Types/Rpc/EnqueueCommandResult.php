<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of queueing a slash command.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EnqueueCommandResult implements Arrayable
{
    /**
     * @param  bool  $queued  Whether the command was accepted into the local execution queue.
     */
    public function __construct(
        public bool $queued,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            queued: Arr::boolean($data, 'queued', false),
        );
    }

    public function toArray(): array
    {
        return [
            'queued' => $this->queued,
        ];
    }
}
