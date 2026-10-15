<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of aborting a manual history compaction.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryAbortManualCompactionResult implements Arrayable
{
    /**
     * @param  bool  $aborted  Whether the manual compaction was aborted
     */
    public function __construct(
        public bool $aborted,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            aborted: Arr::boolean($data, 'aborted'),
        );
    }

    public function toArray(): array
    {
        return [
            'aborted' => $this->aborted,
        ];
    }
}
