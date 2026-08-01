<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HistoryRewindUnavailableReason;

/**
 * Result of previewing the file changes a rewind would restore.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryPreviewRewindResult implements Arrayable
{
    /**
     * @param  bool  $available  Whether a preview could be produced.
     * @param  int  $fileCount  Number of files the rewind would affect.
     * @param  array<HistoryRewindFilePreview>  $files  Per-file previews.
     * @param  HistoryRewindUnavailableReason|string|null  $reason  Why the preview is unavailable, if any.
     */
    public function __construct(
        public bool $available,
        public int $fileCount,
        public array $files,
        public HistoryRewindUnavailableReason|string|null $reason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            available: Arr::boolean($data, 'available', false),
            fileCount: Arr::integer($data, 'fileCount', 0),
            files: array_map(
                fn ($file) => $file instanceof HistoryRewindFilePreview ? $file : HistoryRewindFilePreview::fromArray($file),
                $data['files'] ?? [],
            ),
            reason: isset($data['reason'])
                ? ($data['reason'] instanceof HistoryRewindUnavailableReason
                    ? $data['reason']
                    : HistoryRewindUnavailableReason::from($data['reason']))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'available' => $this->available,
            'fileCount' => $this->fileCount,
            'files' => array_map(fn (HistoryRewindFilePreview $file) => $file->toArray(), $this->files),
            'reason' => $this->reason instanceof HistoryRewindUnavailableReason ? $this->reason->value : $this->reason,
        ], fn ($v) => $v !== null);
    }
}
