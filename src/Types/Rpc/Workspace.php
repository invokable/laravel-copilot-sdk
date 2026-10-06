<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Workspace metadata.
 *
 * @deprecated Legacy summary and PR-dismissal values remain readable for compatibility but are not sent
 *             in the current workspace wire schema.
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
        public ?bool $prCreateSyncDismissed = null,
        public ?bool $chronicleSyncDismissed = null,
        public ?string $clientName = null,
        public ?bool $remoteSteerable = null,
        public ?bool $userNamed = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
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
            prCreateSyncDismissed: $data['pr_create_sync_dismissed'] ?? null,
            chronicleSyncDismissed: $data['chronicle_sync_dismissed'] ?? null,
            clientName: $data['client_name'] ?? null,
            remoteSteerable: $data['remote_steerable'] ?? null,
            userNamed: $data['user_named'] ?? null,
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
            'name' => $this->name,
            'summary_count' => $this->summaryCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'mc_task_id' => $this->mcTaskId,
            'mc_session_id' => $this->mcSessionId,
            'mc_last_event_id' => $this->mcLastEventId,
            'chronicle_sync_dismissed' => $this->chronicleSyncDismissed,
            'client_name' => $this->clientName,
            'remote_steerable' => $this->remoteSteerable,
            'user_named' => $this->userNamed,
        ];
    }
}
