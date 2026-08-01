<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A page of factory progress records.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryProgressPage implements Arrayable
{
    /**
     * @param  array<FactoryProgressLine>  $records  Progress records in the page, ordered by sequence.
     * @param  ?int  $oldestSeq  Oldest sequence number in the page, or null when empty.
     * @param  ?int  $newestSeq  Newest sequence number in the page, or null when empty.
     * @param  bool  $hasMoreOlder  Whether older records exist before this page.
     * @param  bool  $hasMoreNewer  Whether newer records exist after this page.
     * @param  int  $revision  Run revision the page was observed at.
     */
    public function __construct(
        public array $records,
        public ?int $oldestSeq,
        public ?int $newestSeq,
        public bool $hasMoreOlder,
        public bool $hasMoreNewer,
        public int $revision,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            records: array_map(
                fn ($record) => $record instanceof FactoryProgressLine ? $record : FactoryProgressLine::fromArray($record),
                $data['records'] ?? [],
            ),
            oldestSeq: $data['oldestSeq'] ?? null,
            newestSeq: $data['newestSeq'] ?? null,
            hasMoreOlder: Arr::boolean($data, 'hasMoreOlder', false),
            hasMoreNewer: Arr::boolean($data, 'hasMoreNewer', false),
            revision: Arr::integer($data, 'revision', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'records' => array_map(fn (FactoryProgressLine $record) => $record->toArray(), $this->records),
            'oldestSeq' => $this->oldestSeq,
            'newestSeq' => $this->newestSeq,
            'hasMoreOlder' => $this->hasMoreOlder,
            'hasMoreNewer' => $this->hasMoreNewer,
            'revision' => $this->revision,
        ];
    }
}
