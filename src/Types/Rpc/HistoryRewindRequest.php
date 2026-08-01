<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HistoryRewindMode;

/**
 * Parameters for rewinding session history to an earlier point.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryRewindRequest implements Arrayable
{
    /**
     * @param  string  $eventId  Event id to rewind to.
     * @param  HistoryRewindMode|string  $mode  Whether to discard conversation only or also restore files.
     */
    public function __construct(
        public string $eventId,
        public HistoryRewindMode|string $mode,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventId: Arr::string($data, 'eventId'),
            mode: $data['mode'] instanceof HistoryRewindMode ? $data['mode'] : HistoryRewindMode::from($data['mode']),
        );
    }

    public function toArray(): array
    {
        return [
            'eventId' => $this->eventId,
            'mode' => $this->mode instanceof HistoryRewindMode ? $this->mode->value : $this->mode,
        ];
    }
}
