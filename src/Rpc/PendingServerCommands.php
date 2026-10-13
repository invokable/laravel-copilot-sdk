<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CommandList;

/**
 * Pending server-scoped commands RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerCommands
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * List the built-in slash commands available to a new session.
     *
     * @experimental This API is experimental and may change or be removed.
     */
    public function list(): CommandList
    {
        return CommandList::fromArray(
            $this->client->request('commands.list', []),
        );
    }
}
