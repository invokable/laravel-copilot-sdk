<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of reading a directory with type information via SessionFs.
 */
readonly class SessionFsReaddirWithTypesResult implements Arrayable
{
    /**
     * @param  array<SessionFsEntry>  $entries  Directory entries with type information
     */
    public function __construct(
        public array $entries,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entries: array_map(
                fn (array $entry) => SessionFsEntry::fromArray($entry),
                $data['entries'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'entries' => array_map(fn (SessionFsEntry $entry) => $entry->toArray(), $this->entries),
        ];
    }
}
