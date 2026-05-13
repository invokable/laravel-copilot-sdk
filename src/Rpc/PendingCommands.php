<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CommandsHandlePendingCommandRequest;
use Revolution\Copilot\Types\Rpc\CommandsHandlePendingCommandResult;
use Revolution\Copilot\Types\Rpc\CommandsRespondToQueuedCommandRequest;
use Revolution\Copilot\Types\Rpc\CommandsRespondToQueuedCommandResult;

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
    public function handlePendingCommand(CommandsHandlePendingCommandRequest|array $params): CommandsHandlePendingCommandResult
    {
        $paramsArray = ($params instanceof CommandsHandlePendingCommandRequest ? $params : CommandsHandlePendingCommandRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return CommandsHandlePendingCommandResult::fromArray(
            $this->client->request('session.commands.handlePendingCommand', $paramsArray),
        );
    }

    /**
     * Respond to a queued command by providing its result.
     */
    public function respondToQueuedCommand(CommandsRespondToQueuedCommandRequest|array $params): CommandsRespondToQueuedCommandResult
    {
        $paramsArray = ($params instanceof CommandsRespondToQueuedCommandRequest ? $params : CommandsRespondToQueuedCommandRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return CommandsRespondToQueuedCommandResult::fromArray(
            $this->client->request('session.commands.respondToQueuedCommand', $paramsArray),
        );
    }
}
