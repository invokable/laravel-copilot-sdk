<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CanvasCloseRequest;
use Revolution\Copilot\Types\Rpc\CanvasList;
use Revolution\Copilot\Types\Rpc\CanvasListOpenResult;
use Revolution\Copilot\Types\Rpc\CanvasOpenRequest;
use Revolution\Copilot\Types\Rpc\OpenCanvasInstance;

/**
 * Pending canvas RPC operations for a session.
 *
 * @experimental This API is part of an experimental surface and may change or be removed.
 */
class PendingCanvas
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function list(): CanvasList
    {
        return CanvasList::fromArray(
            $this->client->request('session.canvas.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    public function listOpen(): CanvasListOpenResult
    {
        return CanvasListOpenResult::fromArray(
            $this->client->request('session.canvas.listOpen', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    public function open(CanvasOpenRequest|array $params): OpenCanvasInstance
    {
        $paramsArray = ($params instanceof CanvasOpenRequest
            ? $params
            : CanvasOpenRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return OpenCanvasInstance::fromArray(
            $this->client->request('session.canvas.open', $paramsArray),
        );
    }

    public function close(CanvasCloseRequest|array $params): void
    {
        $paramsArray = ($params instanceof CanvasCloseRequest
            ? $params
            : CanvasCloseRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.canvas.close', $paramsArray);
    }

    public function action(): PendingCanvasAction
    {
        return new PendingCanvasAction($this->client, $this->sessionId);
    }
}
