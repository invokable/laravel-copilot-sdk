<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionVisibilityStatus;

/**
 * Desired sharing status for the session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class VisibilitySetRequest implements Arrayable
{
    public function __construct(
        public SessionVisibilityStatus $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: SessionVisibilityStatus::from($data['status']),
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
        ];
    }
}
