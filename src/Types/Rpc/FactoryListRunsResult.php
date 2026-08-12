<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of listing factory runs.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryListRunsResult implements Arrayable
{
    /**
     * @param  array<FactoryRunSummary>  $runs  Observed factory run snapshots.
     */
    public function __construct(
        public array $runs,
        public ?int $oldestSeq = null,
        public ?int $newestSeq = null,
        public ?bool $hasMoreNewer = null,
        public ?int $omittedOlder = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            runs: array_map(
                fn ($run) => $run instanceof FactoryRunSummary ? $run : FactoryRunSummary::fromArray($run),
                $data['runs'] ?? [],
            ),
            oldestSeq: isset($data['oldestSeq']) ? Arr::integer($data, 'oldestSeq') : null,
            newestSeq: isset($data['newestSeq']) ? Arr::integer($data, 'newestSeq') : null,
            hasMoreNewer: isset($data['hasMoreNewer']) ? Arr::boolean($data, 'hasMoreNewer') : null,
            omittedOlder: isset($data['omittedOlder']) ? Arr::integer($data, 'omittedOlder') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'runs' => array_map(fn (FactoryRunSummary $run) => $run->toArray(), $this->runs),
            'oldestSeq' => $this->oldestSeq,
            'newestSeq' => $this->newestSeq,
            'hasMoreNewer' => $this->hasMoreNewer,
            'omittedOlder' => $this->omittedOlder,
        ], fn ($value) => $value !== null);
    }
}
