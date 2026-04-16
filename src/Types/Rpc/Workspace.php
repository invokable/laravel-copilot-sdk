<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Workspace metadata.
 */
readonly class Workspace implements Arrayable
{
    public function __construct(
        public string $id,
        public ?string $cwd = null,
        public ?string $gitRoot = null,
        public ?string $repository = null,
        public ?string $hostType = null,
        public ?string $branch = null,
        public ?string $summary = null,
        public ?string $name = null,
        public ?int $summaryCount = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $mcTaskId = null,
        public ?string $mcSessionId = null,
        public ?string $mcLastEventId = null,
        public ?string $sessionSyncLevel = null,
        public ?bool $prCreateSyncDismissed = null,
        public ?bool $chronicleSyncDismissed = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            cwd: $data['cwd'] ?? null,
            gitRoot: $data['git_root'] ?? null,
            repository: $data['repository'] ?? null,
            hostType: $data['host_type'] ?? null,
            branch: $data['branch'] ?? null,
            summary: $data['summary'] ?? null,
            name: $data['name'] ?? null,
            summaryCount: $data['summary_count'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            mcTaskId: $data['mc_task_id'] ?? null,
            mcSessionId: $data['mc_session_id'] ?? null,
            mcLastEventId: $data['mc_last_event_id'] ?? null,
            sessionSyncLevel: $data['session_sync_level'] ?? null,
            prCreateSyncDismissed: $data['pr_create_sync_dismissed'] ?? null,
            chronicleSyncDismissed: $data['chronicle_sync_dismissed'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'cwd' => $this->cwd,
            'git_root' => $this->gitRoot,
            'repository' => $this->repository,
            'host_type' => $this->hostType,
            'branch' => $this->branch,
            'summary' => $this->summary,
            'name' => $this->name,
            'summary_count' => $this->summaryCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'mc_task_id' => $this->mcTaskId,
            'mc_session_id' => $this->mcSessionId,
            'mc_last_event_id' => $this->mcLastEventId,
            'session_sync_level' => $this->sessionSyncLevel,
            'pr_create_sync_dismissed' => $this->prCreateSyncDismissed,
            'chronicle_sync_dismissed' => $this->chronicleSyncDismissed,
        ];
    }
}
