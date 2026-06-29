<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\VisibilityGetResult;
use Revolution\Copilot\Types\Rpc\VisibilitySetRequest;
use Revolution\Copilot\Types\Rpc\VisibilitySetResult;

/**
 * Pending session visibility RPC operations (Mission Control sharing status).
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingVisibility
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Returns the session's current Mission Control sharing status and shareable GitHub URL.
     */
    public function get(): VisibilityGetResult
    {
        return VisibilityGetResult::fromArray(
            $this->client->request('session.visibility.get', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Sets the session's Mission Control sharing status.
     */
    public function set(VisibilitySetRequest|array $params): VisibilitySetResult
    {
        $paramsArray = ($params instanceof VisibilitySetRequest ? $params : VisibilitySetRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return VisibilitySetResult::fromArray(
            $this->client->request('session.visibility.set', $paramsArray),
        );
    }
}
