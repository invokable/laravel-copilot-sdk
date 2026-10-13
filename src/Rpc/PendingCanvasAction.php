<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CanvasActionInvokeRequest;
use Revolution\Copilot\Types\Rpc\CanvasActionInvokeResult;

/**
 * Pending canvas action RPC operations for a session.
 *
 * @experimental This API is part of an experimental surface and may change or be removed.
 */
class PendingCanvasAction
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function invoke(CanvasActionInvokeRequest|array $params): CanvasActionInvokeResult
    {
        $paramsArray = ($params instanceof CanvasActionInvokeRequest
            ? $params
            : CanvasActionInvokeRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return CanvasActionInvokeResult::fromArray(
            $this->client->request('session.canvas.action.invoke', $paramsArray),
        );
    }
}
