<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceCreateFileParams;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceListFilesResult;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceReadFileParams;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceReadFileResult;

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
    public function listFiles(): SessionWorkspaceListFilesResult
    {
        return SessionWorkspaceListFilesResult::fromArray(
            $this->client->request('session.workspace.listFiles', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Read a file from the workspace.
     */
    public function readFile(SessionWorkspaceReadFileParams|array $params): SessionWorkspaceReadFileResult
    {
        $paramsArray = $params instanceof SessionWorkspaceReadFileParams ? $params->toArray() : $params;
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionWorkspaceReadFileResult::fromArray(
            $this->client->request('session.workspace.readFile', $paramsArray),
        );
    }

    /**
     * Create a file in the workspace.
     */
    public function createFile(SessionWorkspaceCreateFileParams|array $params): array
    {
        $paramsArray = $params instanceof SessionWorkspaceCreateFileParams ? $params->toArray() : $params;
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.workspace.createFile', $paramsArray);
    }
}
