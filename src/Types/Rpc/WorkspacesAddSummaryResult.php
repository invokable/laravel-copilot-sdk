<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Persisted summary metadata and refreshed workspace metadata.
 *
 * @experimental
 */
readonly class WorkspacesAddSummaryResult implements Arrayable
{
    public function __construct(
        public ?WorkspacesAddSummaryResultSummary $summary = null,
        public ?WorkspacesAddSummaryResultWorkspace $workspace = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            summary: isset($data['summary'])
                ? WorkspacesAddSummaryResultSummary::fromArray($data['summary'])
                : null,
            workspace: isset($data['workspace'])
                ? WorkspacesAddSummaryResultWorkspace::fromArray($data['workspace'])
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'summary' => $this->summary?->toArray(),
            'workspace' => $this->workspace?->toArray(),
        ];
    }
}
