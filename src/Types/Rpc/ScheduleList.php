<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Snapshot of the currently active recurring prompts for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleList implements Arrayable
{
    /**
     * @param  array<ScheduleEntry>  $entries  Active scheduled prompts, ordered by id
     */
    public function __construct(
        public array $entries = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entries: array_map(
                fn (array $entry) => ScheduleEntry::fromArray($entry),
                $data['entries'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'entries' => array_map(fn ($entry) => $entry->toArray(), $this->entries),
        ];
    }
}
