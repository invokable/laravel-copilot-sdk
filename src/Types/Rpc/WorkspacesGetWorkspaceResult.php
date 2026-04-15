<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting current workspace metadata.
 */
readonly class WorkspacesGetWorkspaceResult implements Arrayable
{
    /**
     * @param  ?Workspace  $workspace  Current workspace metadata, or null if not available
     */
    public function __construct(
        public ?Workspace $workspace = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            workspace: isset($data['workspace']) ? Workspace::fromArray($data['workspace']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'workspace' => $this->workspace?->toArray(),
        ];
    }
}
