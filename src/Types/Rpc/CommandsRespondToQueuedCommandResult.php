<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of responding to a queued command.
 */
readonly class CommandsRespondToQueuedCommandResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether the response was accepted (false if the requestId was not found or already resolved)
     */
    public function __construct(
        public bool $success,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: Arr::boolean($data, 'success', false),
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
        ];
    }
}
