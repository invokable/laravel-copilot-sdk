<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CommandList;
use Revolution\Copilot\Types\Rpc\CommandsHandlePendingCommandRequest;
use Revolution\Copilot\Types\Rpc\CommandsHandlePendingCommandResult;
use Revolution\Copilot\Types\Rpc\CommandsInvokeRequest;
use Revolution\Copilot\Types\Rpc\CommandsListRequest;
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
     * List slash commands available in the session.
     */
    public function list(CommandsListRequest|array|null $params = null): CommandList
    {
        $paramsArray = [];

        if ($params !== null) {
            $paramsArray = ($params instanceof CommandsListRequest ? $params : CommandsListRequest::fromArray($params))->toArray();
        }

        $paramsArray['sessionId'] = $this->sessionId;

        return CommandList::fromArray(
            $this->client->request('session.commands.list', $paramsArray),
        );
    }

    /**
     * Invoke a slash command in the session.
     */
    public function invoke(CommandsInvokeRequest|array $params): array
    {
        $paramsArray = ($params instanceof CommandsInvokeRequest ? $params : CommandsInvokeRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.commands.invoke', $paramsArray);
    }

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
