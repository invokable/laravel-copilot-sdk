<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Metadata for a persisted summary.
 *
 * @experimental
 */
readonly class WorkspacesAddSummaryResultSummary implements Arrayable
{
    /**
     * The current upstream schema leaves this result object open and fieldless.
     *
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(data: $data);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
