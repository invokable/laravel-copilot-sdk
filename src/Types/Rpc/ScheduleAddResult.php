<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of adding a schedule.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleAddResult implements Arrayable
{
    /**
     * @param  ScheduleEntry|array|null  $entry  The created schedule entry, when successful.
     * @param  ?string  $error  Error message when the schedule could not be added.
     */
    public function __construct(
        public ScheduleEntry|array|null $entry = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $entry = $data['entry'] ?? null;

        return new self(
            entry: $entry !== null
                ? ($entry instanceof ScheduleEntry ? $entry : ScheduleEntry::fromArray($entry))
                : null,
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        $entry = $this->entry instanceof ScheduleEntry ? $this->entry->toArray() : $this->entry;

        return array_filter([
            'entry' => $entry,
            'error' => $this->error,
        ], fn ($v) => $v !== null);
    }
}
