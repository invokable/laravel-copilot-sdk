<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Aggregated code change metrics.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CodeChanges implements Arrayable
{
    /**
     * @param  int  $linesAdded  Total lines of code added
     * @param  int  $linesRemoved  Total lines of code removed
     * @param  int  $filesModifiedCount  Number of distinct files modified
     */
    public function __construct(
        public int $linesAdded,
        public int $linesRemoved,
        public int $filesModifiedCount,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            linesAdded: $data['linesAdded'],
            linesRemoved: $data['linesRemoved'],
            filesModifiedCount: $data['filesModifiedCount'],
        );
    }

    public function toArray(): array
    {
        return [
            'linesAdded' => $this->linesAdded,
            'linesRemoved' => $this->linesRemoved,
            'filesModifiedCount' => $this->filesModifiedCount,
        ];
    }
}
