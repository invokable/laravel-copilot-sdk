<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\WorkspacesCreateFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesGetWorkspaceResult;
use Revolution\Copilot\Types\Rpc\WorkspacesListFilesResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileResult;

/**
 * Pending workspaces RPC operations for a session.
 */
class PendingWorkspaces
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get current workspace metadata.
     */
    public function getWorkspace(): WorkspacesGetWorkspaceResult
    {
        return WorkspacesGetWorkspaceResult::fromArray(
            $this->client->request('session.workspaces.getWorkspace', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * List files in the workspace.
     */
    public function listFiles(): WorkspacesListFilesResult
    {
        return WorkspacesListFilesResult::fromArray(
            $this->client->request('session.workspaces.listFiles', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Read a file from the workspace.
     */
    public function readFile(WorkspacesReadFileRequest|array $params): WorkspacesReadFileResult
    {
        $paramsArray = ($params instanceof WorkspacesReadFileRequest ? $params : WorkspacesReadFileRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspacesReadFileResult::fromArray(
            $this->client->request('session.workspaces.readFile', $paramsArray),
        );
    }

    /**
     * Create a file in the workspace.
     */
    public function createFile(WorkspacesCreateFileRequest|array $params): array
    {
        $paramsArray = ($params instanceof WorkspacesCreateFileRequest ? $params : WorkspacesCreateFileRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.workspaces.createFile', $paramsArray);
    }
}
