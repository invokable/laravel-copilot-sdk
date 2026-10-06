<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\GitCurrentBranchRemoteResult;
use Revolution\Copilot\Types\Rpc\GitReposFromRemotesResult;
use Revolution\Copilot\Types\Rpc\SessionWorkingDirectoryContext;

/** Server-side working-directory and Git remote queries. */
class PendingServerGit
{
    public function __construct(protected JsonRpcClient $client) {}

    public function currentBranchRemote(string $cwd): GitCurrentBranchRemoteResult
    {
        return GitCurrentBranchRemoteResult::fromArray(
            $this->client->request('git.currentBranchRemote', ['cwd' => $cwd]),
        );
    }

    public function workingDirectoryContext(string $cwd): SessionWorkingDirectoryContext
    {
        return SessionWorkingDirectoryContext::fromArray(
            $this->client->request('git.workingDirectoryContext', ['cwd' => $cwd]),
        );
    }

    public function reposFromRemotes(string $cwd): GitReposFromRemotesResult
    {
        return GitReposFromRemotesResult::fromArray(
            $this->client->request('git.reposFromRemotes', ['cwd' => $cwd]),
        );
    }
}
