<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Cancellation result for a user-requested shell command.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CancelUserRequestedShellCommandResult implements Arrayable
{
    /**
     * @param  bool  $cancelled  Whether an in-flight execution was found and signalled to cancel.
     */
    public function __construct(
        public bool $cancelled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cancelled: Arr::boolean($data, 'cancelled', false),
        );
    }

    public function toArray(): array
    {
        return [
            'cancelled' => $this->cancelled,
        ];
    }
}
