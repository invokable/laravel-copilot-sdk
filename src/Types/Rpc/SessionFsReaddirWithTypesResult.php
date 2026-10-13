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
     * @param  ?SessionFsError  $error  Structured provider error, if the read failed
     */
    public function __construct(
        public array $entries,
        public ?SessionFsError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entries: array_map(
                fn (array $entry) => SessionFsEntry::fromArray($entry),
                $data['entries'] ?? [],
            ),
            error: isset($data['error']) ? SessionFsError::fromArray($data['error']) : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'entries' => array_map(fn (SessionFsEntry $entry) => $entry->toArray(), $this->entries),
        ];

        if ($this->error !== null) {
            $result['error'] = $this->error->toArray();
        }

        return $result;
    }
}
