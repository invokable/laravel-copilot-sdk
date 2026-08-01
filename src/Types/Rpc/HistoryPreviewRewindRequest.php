<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for previewing the file changes a rewind would restore.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryPreviewRewindRequest implements Arrayable
{
    /**
     * @param  string  $eventId  Event id to preview a rewind to.
     */
    public function __construct(
        public string $eventId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventId: Arr::string($data, 'eventId'),
        );
    }

    public function toArray(): array
    {
        return [
            'eventId' => $this->eventId,
        ];
    }
}
