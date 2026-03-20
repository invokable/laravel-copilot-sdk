<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of handling a pending command.
 */
readonly class SessionCommandsHandlePendingCommandResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether the command was handled successfully
     */
    public function __construct(
        public bool $success,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
        ];
    }
}
