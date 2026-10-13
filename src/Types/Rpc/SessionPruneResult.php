<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of pruning persisted sessions.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionPruneResult implements Arrayable
{
    /**
     * @param  array<string>  $deleted
     * @param  array<string>  $candidates
     * @param  array<string>  $skipped
     */
    public function __construct(
        public array $deleted,
        public array $candidates,
        public array $skipped,
        public int $freedBytes,
        public bool $dryRun,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deleted: $data['deleted'] ?? [],
            candidates: $data['candidates'] ?? [],
            skipped: $data['skipped'] ?? [],
            freedBytes: Arr::integer($data, 'freedBytes', 0),
            dryRun: Arr::boolean($data, 'dryRun', false),
        );
    }

    public function toArray(): array
    {
        return [
            'deleted' => $this->deleted,
            'candidates' => $this->candidates,
            'skipped' => $this->skipped,
            'freedBytes' => $this->freedBytes,
            'dryRun' => $this->dryRun,
        ];
    }
}
