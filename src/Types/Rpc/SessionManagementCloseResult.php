<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Completion of the existing non-deleting close lifecycle.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementCloseResult implements Arrayable
{
    public function __construct(
        public bool $closed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            closed: $data['closed'],
        );
    }

    public function toArray(): array
    {
        return [
            'closed' => $this->closed,
        ];
    }
}
