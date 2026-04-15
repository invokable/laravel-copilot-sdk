<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ModeSetRequest;

/**
 * Pending mode RPC operations for a session.
 */
class PendingMode
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get the current mode.
     *
     * Returns the session mode string directly (e.g. "interactive", "plan", "autopilot").
     */
    public function get(): string
    {
        return $this->client->request('session.mode.get', [
            'sessionId' => $this->sessionId,
        ]);
    }

    /**
     * Set the mode.
     *
     * @param  ModeSetRequest|array{mode: string}  $params
     */
    public function set(ModeSetRequest|array $params): void
    {
        $paramsArray = ($params instanceof ModeSetRequest ? $params : ModeSetRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.mode.set', $paramsArray);
    }
}
