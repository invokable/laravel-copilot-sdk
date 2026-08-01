<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

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
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            runs: array_map(
                fn ($run) => $run instanceof FactoryRunSummary ? $run : FactoryRunSummary::fromArray($run),
                $data['runs'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'runs' => array_map(fn (FactoryRunSummary $run) => $run->toArray(), $this->runs),
        ];
    }
}
