<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\WorkspaceDiffResult;
use Revolution\Copilot\Types\Rpc\WorkspacesAddSummaryRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesAddSummaryResult;
use Revolution\Copilot\Types\Rpc\WorkspacesAutopilotObjectiveExistsResult;
use Revolution\Copilot\Types\Rpc\WorkspacesCreateDirectoryRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesCreateFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesDeleteAutopilotObjectiveResult;
use Revolution\Copilot\Types\Rpc\WorkspacesDiffRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesEnsureRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesGetWorkspaceResult;
use Revolution\Copilot\Types\Rpc\WorkspacesListCheckpointsResult;
use Revolution\Copilot\Types\Rpc\WorkspacesListFilesResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadAutopilotObjectiveResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadCheckpointRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesReadCheckpointResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileResult;
use Revolution\Copilot\Types\Rpc\WorkspacesRemovePathRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesRenamePathRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesSaveLargePasteRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesSaveLargePasteResult;
use Revolution\Copilot\Types\Rpc\WorkspacesStatFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesStatFileResult;
use Revolution\Copilot\Types\Rpc\WorkspacesTruncateSummariesRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesUpdateMetadataRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesWriteAutopilotObjectiveRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesWriteAutopilotObjectiveResult;

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
     * Update workspace metadata from session context and an optional display name.
     */
    public function updateMetadata(WorkspacesUpdateMetadataRequest|array $params = []): WorkspacesGetWorkspaceResult
    {
        $paramsArray = ($params instanceof WorkspacesUpdateMetadataRequest
            ? $params
            : WorkspacesUpdateMetadataRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspacesGetWorkspaceResult::fromArray(
            $this->client->request('session.workspaces.updateMetadata', $paramsArray),
        );
    }

    /**
     * Ensure a local session workspace exists and return its metadata.
     */
    public function ensure(WorkspacesEnsureRequest|array $params = []): WorkspacesGetWorkspaceResult
    {
        $paramsArray = ($params instanceof WorkspacesEnsureRequest
            ? $params
            : WorkspacesEnsureRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspacesGetWorkspaceResult::fromArray(
            $this->client->request('session.workspaces.ensure', $paramsArray),
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

    /**
     * Returns metadata for a file or directory in the session workspace files directory.
     */
    public function statFile(WorkspacesStatFileRequest|array $params): WorkspacesStatFileResult
    {
        $paramsArray = ($params instanceof WorkspacesStatFileRequest ? $params : WorkspacesStatFileRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspacesStatFileResult::fromArray(
            $this->client->request('session.workspaces.statFile', $paramsArray),
        );
    }

    /**
     * Create a directory in the session workspace files directory.
     */
    public function createDirectory(WorkspacesCreateDirectoryRequest|array $params): array
    {
        $paramsArray = ($params instanceof WorkspacesCreateDirectoryRequest ? $params : WorkspacesCreateDirectoryRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.workspaces.createDirectory', $paramsArray);
    }

    /**
     * Remove a file or directory from the session workspace files directory.
     */
    public function removePath(WorkspacesRemovePathRequest|array $params): array
    {
        $paramsArray = ($params instanceof WorkspacesRemovePathRequest ? $params : WorkspacesRemovePathRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.workspaces.removePath', $paramsArray);
    }

    /**
     * Rename a file or directory within the session workspace files directory.
     */
    public function renamePath(WorkspacesRenamePathRequest|array $params): array
    {
        $paramsArray = ($params instanceof WorkspacesRenamePathRequest ? $params : WorkspacesRenamePathRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.workspaces.renamePath', $paramsArray);
    }

    /**
     * List session workspace checkpoints in chronological order.
     */
    public function listCheckpoints(): WorkspacesListCheckpointsResult
    {
        return WorkspacesListCheckpointsResult::fromArray(
            $this->client->request('session.workspaces.listCheckpoints', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Read a workspace checkpoint by its number.
     */
    public function readCheckpoint(WorkspacesReadCheckpointRequest|array $params): WorkspacesReadCheckpointResult
    {
        $paramsArray = ($params instanceof WorkspacesReadCheckpointRequest
            ? $params
            : WorkspacesReadCheckpointRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspacesReadCheckpointResult::fromArray(
            $this->client->request('session.workspaces.readCheckpoint', $paramsArray),
        );
    }

    /**
     * Persist a compaction summary checkpoint.
     */
    public function addSummary(WorkspacesAddSummaryRequest|array $params): WorkspacesAddSummaryResult
    {
        $paramsArray = ($params instanceof WorkspacesAddSummaryRequest
            ? $params
            : WorkspacesAddSummaryRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspacesAddSummaryResult::fromArray(
            $this->client->request('session.workspaces.addSummary', $paramsArray),
        );
    }

    /**
     * Keep only the newest summaries after a rollback.
     */
    public function truncateSummaries(WorkspacesTruncateSummariesRequest|array $params): WorkspacesGetWorkspaceResult
    {
        $paramsArray = ($params instanceof WorkspacesTruncateSummariesRequest
            ? $params
            : WorkspacesTruncateSummariesRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspacesGetWorkspaceResult::fromArray(
            $this->client->request('session.workspaces.truncateSummaries', $paramsArray),
        );
    }

    /**
     * Read the autopilot objective state file, if present.
     */
    public function readAutopilotObjective(): WorkspacesReadAutopilotObjectiveResult
    {
        return WorkspacesReadAutopilotObjectiveResult::fromArray(
            $this->client->request('session.workspaces.readAutopilotObjective', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Write the autopilot objective state file.
     */
    public function writeAutopilotObjective(WorkspacesWriteAutopilotObjectiveRequest|array $params): WorkspacesWriteAutopilotObjectiveResult
    {
        $paramsArray = ($params instanceof WorkspacesWriteAutopilotObjectiveRequest
            ? $params
            : WorkspacesWriteAutopilotObjectiveRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspacesWriteAutopilotObjectiveResult::fromArray(
            $this->client->request('session.workspaces.writeAutopilotObjective', $paramsArray),
        );
    }

    /**
     * Delete the autopilot objective state file.
     */
    public function deleteAutopilotObjective(): WorkspacesDeleteAutopilotObjectiveResult
    {
        return WorkspacesDeleteAutopilotObjectiveResult::fromArray(
            $this->client->request('session.workspaces.deleteAutopilotObjective', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Check whether an autopilot objective state file exists.
     */
    public function autopilotObjectiveExists(): WorkspacesAutopilotObjectiveExistsResult
    {
        return WorkspacesAutopilotObjectiveExistsResult::fromArray(
            $this->client->request('session.workspaces.autopilotObjectiveExists', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Save pasted content as a UTF-8 file in the session workspace.
     */
    public function saveLargePaste(WorkspacesSaveLargePasteRequest|array $params): WorkspacesSaveLargePasteResult
    {
        $paramsArray = ($params instanceof WorkspacesSaveLargePasteRequest
            ? $params
            : WorkspacesSaveLargePasteRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspacesSaveLargePasteResult::fromArray(
            $this->client->request('session.workspaces.saveLargePaste', $paramsArray),
        );
    }

    /**
     * Compute a workspace diff for the requested mode.
     */
    public function diff(WorkspacesDiffRequest|array $params): WorkspaceDiffResult
    {
        $paramsArray = ($params instanceof WorkspacesDiffRequest
            ? $params
            : WorkspacesDiffRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return WorkspaceDiffResult::fromArray(
            $this->client->request('session.workspaces.diff', $paramsArray),
        );
    }
}
