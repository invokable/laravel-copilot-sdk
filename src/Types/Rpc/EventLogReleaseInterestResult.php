<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of releasing an event-interest registration.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EventLogReleaseInterestResult implements Arrayable
{
    public function __construct(
        public bool $success,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: (bool) ($data['success'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
        ];
    }
}
