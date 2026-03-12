<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionShellExecParams;
use Revolution\Copilot\Types\Rpc\SessionShellExecResult;
use Revolution\Copilot\Types\Rpc\SessionShellKillParams;
use Revolution\Copilot\Types\Rpc\SessionShellKillResult;

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
    public function exec(SessionShellExecParams|array $params): SessionShellExecResult
    {
        $paramsArray = ($params instanceof SessionShellExecParams ? $params : SessionShellExecParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionShellExecResult::fromArray(
            $this->client->request('session.shell.exec', $paramsArray),
        );
    }

    /**
     * Kill a running shell process.
     */
    public function kill(SessionShellKillParams|array $params): SessionShellKillResult
    {
        $paramsArray = ($params instanceof SessionShellKillParams ? $params : SessionShellKillParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionShellKillResult::fromArray(
            $this->client->request('session.shell.kill', $paramsArray),
        );
    }
}
