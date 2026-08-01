<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HistoryFileRestoreSkipReason;

/**
 * A captured file that was not restored during a rewind.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistorySkippedFileRestore implements Arrayable
{
    /**
     * @param  string  $path  Path of the file that was not restored.
     * @param  HistoryFileRestoreSkipReason|string  $reason  Reason the file was skipped.
     */
    public function __construct(
        public string $path,
        public HistoryFileRestoreSkipReason|string $reason,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: Arr::string($data, 'path', ''),
            reason: $data['reason'] instanceof HistoryFileRestoreSkipReason
                ? $data['reason']
                : HistoryFileRestoreSkipReason::from($data['reason']),
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'reason' => $this->reason instanceof HistoryFileRestoreSkipReason ? $this->reason->value : $this->reason,
        ];
    }
}
