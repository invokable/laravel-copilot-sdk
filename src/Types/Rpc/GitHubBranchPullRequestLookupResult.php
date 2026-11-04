<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\GitHubBranchPullRequestLookupStatus;

readonly class GitHubBranchPullRequestLookupResult implements Arrayable
{
    public function __construct(
        public GitHubBranchPullRequestLookupStatus|string $status,
        public ?array $pullRequest = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = Arr::string($data, 'status', '');

        return new self(
            status: GitHubBranchPullRequestLookupStatus::tryFrom($status) ?? $status,
            pullRequest: isset($data['pullRequest']) ? Arr::array($data, 'pullRequest') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status instanceof GitHubBranchPullRequestLookupStatus
                ? $this->status->value
                : $this->status,
            'pullRequest' => $this->pullRequest,
        ];
    }
}
