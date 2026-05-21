<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionAuthStatus;
use Revolution\Copilot\Types\Rpc\SessionSetCredentialsParams;
use Revolution\Copilot\Types\Rpc\SessionSetCredentialsResult;

/**
 * Pending session authentication RPC operations.
 */
class PendingSessionAuth
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get the authentication status for this session.
     */
    public function getStatus(): SessionAuthStatus
    {
        return SessionAuthStatus::fromArray(
            $this->client->request('session.auth.getStatus', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Update authentication credentials for this session.
     */
    public function setCredentials(SessionSetCredentialsParams|array|null $params = null): SessionSetCredentialsResult
    {
        $paramsArray = [];

        if ($params !== null) {
            $paramsArray = ($params instanceof SessionSetCredentialsParams ? $params : SessionSetCredentialsParams::fromArray($params))->toArray();
        }

        $paramsArray['sessionId'] = $this->sessionId;

        return SessionSetCredentialsResult::fromArray(
            $this->client->request('session.auth.setCredentials', $paramsArray),
        );
    }
}
