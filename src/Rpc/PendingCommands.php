<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionCommandsHandlePendingCommandParams;
use Revolution\Copilot\Types\Rpc\SessionCommandsHandlePendingCommandResult;

/**
 * Pending commands RPC operations for a session.
 */
class PendingCommands
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Handle a pending command by providing its result or error.
     */
    public function handlePendingCommand(SessionCommandsHandlePendingCommandParams|array $params): SessionCommandsHandlePendingCommandResult
    {
        $paramsArray = ($params instanceof SessionCommandsHandlePendingCommandParams ? $params : SessionCommandsHandlePendingCommandParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionCommandsHandlePendingCommandResult::fromArray(
            $this->client->request('session.commands.handlePendingCommand', $paramsArray),
        );
    }
}
