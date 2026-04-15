<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\WorkspaceCreateFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspaceListFilesResult;
use Revolution\Copilot\Types\Rpc\WorkspaceReadFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspaceReadFileResult;

/**
 * Pending workspace RPC operations for a session.
 */
class PendingWorkspace
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * List files in the workspace.
     */
    public function listFiles(): WorkspaceListFilesResult
    {
        return WorkspaceListFilesResult::fromArray(
            $this->client->request('session.workspace.listFiles', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Read a file from the workspace.
     */
    public function readFile(WorkspaceReadFileRequest|array $params): WorkspaceReadFileResult
    {
        $paramsArray = ($params instanceof WorkspaceReadFileRequest ? $params : WorkspaceReadFileRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspaceReadFileResult::fromArray(
            $this->client->request('session.workspace.readFile', $paramsArray),
        );
    }

    /**
     * Create a file in the workspace.
     */
    public function createFile(WorkspaceCreateFileRequest|array $params): array
    {
        $paramsArray = ($params instanceof WorkspaceCreateFileRequest ? $params : WorkspaceCreateFileRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.workspace.createFile', $paramsArray);
    }
}
