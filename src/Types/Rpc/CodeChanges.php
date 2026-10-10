<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

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
        public ?array $filesModified = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            linesAdded: Arr::integer($data, 'linesAdded'),
            linesRemoved: Arr::integer($data, 'linesRemoved'),
            filesModifiedCount: Arr::integer($data, 'filesModifiedCount'),
            filesModified: isset($data['filesModified']) ? array_values($data['filesModified']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'linesAdded' => $this->linesAdded,
            'linesRemoved' => $this->linesRemoved,
            'filesModifiedCount' => $this->filesModifiedCount,
            'filesModified' => $this->filesModified,
        ], static fn ($value) => $value !== null);
    }
}
