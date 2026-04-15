<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ShellExecRequest;
use Revolution\Copilot\Types\Rpc\ShellExecResult;
use Revolution\Copilot\Types\Rpc\ShellKillRequest;
use Revolution\Copilot\Types\Rpc\ShellKillResult;

/**
 * Pending shell RPC operations for a session.
 */
class PendingShell
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Execute a shell command in the session.
     *
     * Returns a processId that can be used to track streamed output
     * or kill the process with shell()->kill().
     */
    public function exec(ShellExecRequest|array $params): ShellExecResult
    {
        $paramsArray = ($params instanceof ShellExecRequest ? $params : ShellExecRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ShellExecResult::fromArray(
            $this->client->request('session.shell.exec', $paramsArray),
        );
    }

    /**
     * Kill a running shell process.
     */
    public function kill(ShellKillRequest|array $params): ShellKillResult
    {
        $paramsArray = ($params instanceof ShellKillRequest ? $params : ShellKillRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ShellKillResult::fromArray(
            $this->client->request('session.shell.kill', $paramsArray),
        );
    }
}
