<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of reading a directory via SessionFs.
 */
readonly class SessionFsReaddirResult implements Arrayable
{
    /**
     * @param  array<string>  $entries  Entry names in the directory
     */
    public function __construct(
        public array $entries,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entries: $data['entries'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'entries' => $this->entries,
        ];
    }
}
