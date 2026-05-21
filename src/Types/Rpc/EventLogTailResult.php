<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Snapshot cursor at the current tail of session event history.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EventLogTailResult implements Arrayable
{
    public function __construct(
        public string $cursor,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cursor: $data['cursor'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'cursor' => $this->cursor,
        ];
    }
}
