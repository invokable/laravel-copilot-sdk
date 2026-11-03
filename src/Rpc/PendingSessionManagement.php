<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionManagementCloseRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementCloseResult;
use Revolution\Copilot\Types\Rpc\SessionManagementConfigureRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementListRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementListResult;
use Revolution\Copilot\Types\Rpc\SessionManagementMessageRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementMessageResult;
use Revolution\Copilot\Types\Rpc\SessionManagementPolicy;

/**
 * Pending session management RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSessionManagement
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Updates the session's live policy without changing persistent host settings.
     */
    public function configure(SessionManagementConfigureRequest|array $params): SessionManagementPolicy
    {
        $paramsArray = ($params instanceof SessionManagementConfigureRequest
            ? $params
            : SessionManagementConfigureRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionManagementPolicy::fromArray(
            $this->client->request('session.management.configure', $paramsArray),
        );
    }

    /**
     * Lists other live, opted-in sessions in the same configuration directory.
     */
    public function list(SessionManagementListRequest|array $params): SessionManagementListResult
    {
        $paramsArray = ($params instanceof SessionManagementListRequest
            ? $params
            : SessionManagementListRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionManagementListResult::fromArray(
            $this->client->request('session.management.list', $paramsArray),
        );
    }

    /**
     * Delivers peer text to another opted-in session.
     */
    public function message(SessionManagementMessageRequest|array $params): SessionManagementMessageResult
    {
        $paramsArray = ($params instanceof SessionManagementMessageRequest
            ? $params
            : SessionManagementMessageRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionManagementMessageResult::fromArray(
            $this->client->request('session.management.message', $paramsArray),
        );
    }

    /**
     * Closes a live opted-in descendant without deleting its saved history.
     */
    public function close(SessionManagementCloseRequest|array $params): SessionManagementCloseResult
    {
        $paramsArray = ($params instanceof SessionManagementCloseRequest
            ? $params
            : SessionManagementCloseRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionManagementCloseResult::fromArray(
            $this->client->request('session.management.close', $paramsArray),
        );
    }
}
