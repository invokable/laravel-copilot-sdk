<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\HistoryRewindOutcome;

/**
 * Result of a rewind request.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryRewindResult implements Arrayable
{
    /**
     * @param  HistoryRewindOutcome|string  $outcome  Outcome of the rewind.
     * @param  array<string>  $restoredFiles  Paths of files that were restored.
     * @param  array<HistorySkippedFileRestore>  $skippedFiles  Files that could not be restored.
     * @param  ?int  $eventsRemoved  Number of events discarded, when known.
     * @param  ?string  $error  Error message when the rewind failed.
     */
    public function __construct(
        public HistoryRewindOutcome|string $outcome,
        public array $restoredFiles,
        public array $skippedFiles,
        public ?int $eventsRemoved = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            outcome: $data['outcome'] instanceof HistoryRewindOutcome ? $data['outcome'] : HistoryRewindOutcome::from($data['outcome']),
            restoredFiles: $data['restoredFiles'] ?? [],
            skippedFiles: array_map(
                fn ($file) => $file instanceof HistorySkippedFileRestore ? $file : HistorySkippedFileRestore::fromArray($file),
                $data['skippedFiles'] ?? [],
            ),
            eventsRemoved: $data['eventsRemoved'] ?? null,
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'outcome' => $this->outcome instanceof HistoryRewindOutcome ? $this->outcome->value : $this->outcome,
            'restoredFiles' => $this->restoredFiles,
            'skippedFiles' => array_map(fn (HistorySkippedFileRestore $file) => $file->toArray(), $this->skippedFiles),
            'eventsRemoved' => $this->eventsRemoved,
            'error' => $this->error,
        ], fn ($v) => $v !== null);
    }
}
