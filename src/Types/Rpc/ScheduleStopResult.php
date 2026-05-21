<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of removing a scheduled prompt by id.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleStopResult implements Arrayable
{
    /**
     * @param  ?ScheduleEntry  $entry  The removed entry, or null if no entry matched
     */
    public function __construct(
        public ?ScheduleEntry $entry = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entry: isset($data['entry']) ? ScheduleEntry::fromArray($data['entry']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'entry' => $this->entry?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
