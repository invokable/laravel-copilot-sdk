<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for resolving a cloud coding-agent task to an openable session.
 *
 * @experimental
 */
readonly class CloudTasksResolveTaskIdParams implements Arrayable
{
    /**
     * @param  string[]  $ignoredLocalSessionIds
     */
    public function __construct(
        public string $taskId,
        public mixed $authInfo,
        public string $integrationId,
        public bool $allowEmptyCliTask,
        public bool $hasRemoteSessionManager,
        public array $ignoredLocalSessionIds,
        public ?string $authContextId = null,
        public ?string $debugGithubApiUrl = null,
        public ?string $sessionsHome = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            taskId: $data['taskId'],
            authInfo: $data['authInfo'],
            authContextId: $data['authContextId'] ?? null,
            integrationId: $data['integrationId'],
            debugGithubApiUrl: $data['debugGithubApiUrl'] ?? null,
            sessionsHome: $data['sessionsHome'] ?? null,
            allowEmptyCliTask: $data['allowEmptyCliTask'],
            hasRemoteSessionManager: $data['hasRemoteSessionManager'],
            ignoredLocalSessionIds: $data['ignoredLocalSessionIds'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'taskId' => $this->taskId,
            'authInfo' => $this->authInfo,
            'authContextId' => $this->authContextId,
            'integrationId' => $this->integrationId,
            'debugGithubApiUrl' => $this->debugGithubApiUrl,
            'sessionsHome' => $this->sessionsHome,
            'allowEmptyCliTask' => $this->allowEmptyCliTask,
            'hasRemoteSessionManager' => $this->hasRemoteSessionManager,
            'ignoredLocalSessionIds' => $this->ignoredLocalSessionIds,
        ], fn ($value, $key): bool => $key === 'authInfo' || $value !== null, ARRAY_FILTER_USE_BOTH);
    }
}
