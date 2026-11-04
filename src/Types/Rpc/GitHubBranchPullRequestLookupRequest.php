<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class GitHubBranchPullRequestLookupRequest implements Arrayable
{
    /**
     * @param  GitHubRepositoryOwnerName[]  $baseRepositories
     */
    public function __construct(
        public mixed $authInfo,
        public string $headOwner,
        public array $baseRepositories,
        public string $branch,
        public bool $includeClosed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            authInfo: $data['authInfo'] ?? null,
            headOwner: Arr::string($data, 'headOwner', ''),
            baseRepositories: array_map(
                static fn (array $repository): GitHubRepositoryOwnerName => GitHubRepositoryOwnerName::fromArray($repository),
                Arr::array($data, 'baseRepositories', []),
            ),
            branch: Arr::string($data, 'branch', ''),
            includeClosed: Arr::boolean($data, 'includeClosed', false),
        );
    }

    public function toArray(): array
    {
        return [
            'authInfo' => $this->authInfo,
            'headOwner' => $this->headOwner,
            'baseRepositories' => array_map(
                static fn (GitHubRepositoryOwnerName $repository): array => $repository->toArray(),
                $this->baseRepositories,
            ),
            'branch' => $this->branch,
            'includeClosed' => $this->includeClosed,
        ];
    }
}
