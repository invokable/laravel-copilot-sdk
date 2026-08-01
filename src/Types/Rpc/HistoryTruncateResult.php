<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of session history truncation.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryTruncateResult implements Arrayable
{
    /**
     * @param  int  $eventsRemoved  Number of events that were removed
     * @param  ?bool  $checkpointCleanupFailed  Whether removing associated checkpoints failed
     * @param  ?string  $checkpointCleanupError  Error message when checkpoint cleanup failed
     */
    public function __construct(
        public int $eventsRemoved,
        public ?bool $checkpointCleanupFailed = null,
        public ?string $checkpointCleanupError = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventsRemoved: Arr::integer($data, 'eventsRemoved'),
            checkpointCleanupFailed: $data['checkpointCleanupFailed'] ?? null,
            checkpointCleanupError: $data['checkpointCleanupError'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'eventsRemoved' => $this->eventsRemoved,
            'checkpointCleanupFailed' => $this->checkpointCleanupFailed,
            'checkpointCleanupError' => $this->checkpointCleanupError,
        ], fn ($v) => $v !== null);
    }
}
