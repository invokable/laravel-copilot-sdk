<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Workspace checkpoints in chronological order.
 *
 * @experimental
 */
readonly class WorkspacesListCheckpointsResult implements Arrayable
{
    /**
     * @param  array<WorkspacesCheckpoints>  $checkpoints
     */
    public function __construct(
        public array $checkpoints,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            checkpoints: array_map(
                static fn (array $checkpoint): WorkspacesCheckpoints => WorkspacesCheckpoints::fromArray($checkpoint),
                $data['checkpoints'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'checkpoints' => array_map(
                static fn (WorkspacesCheckpoints $checkpoint): array => $checkpoint->toArray(),
                $this->checkpoints,
            ),
        ];
    }
}
