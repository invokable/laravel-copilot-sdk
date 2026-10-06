<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Rollback point for local workspace summaries.
 *
 * @experimental
 */
readonly class WorkspacesTruncateSummariesRequest implements Arrayable
{
    public function __construct(
        public int $keepCount,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(keepCount: Arr::integer($data, 'keepCount'));
    }

    public function toArray(): array
    {
        return ['keepCount' => $this->keepCount];
    }
}
