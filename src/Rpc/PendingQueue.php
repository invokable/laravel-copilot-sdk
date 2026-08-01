<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\QueueBeginDeferredIdleDrainRequest;
use Revolution\Copilot\Types\Rpc\QueueBeginDeferredIdleDrainResult;
use Revolution\Copilot\Types\Rpc\QueueFinishDeferredIdleDrainRequest;
use Revolution\Copilot\Types\Rpc\QueueFinishDeferredIdleDrainResult;
use Revolution\Copilot\Types\Rpc\QueuePendingItemsResult;
use Revolution\Copilot\Types\Rpc\QueueRemoveMostRecentResult;

/**
 * Pending queue RPC operations for a session.
 */
class PendingQueue
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Returns the local session's pending user-facing queued items and steering messages.
     */
    public function pendingItems(): QueuePendingItemsResult
    {
        return QueuePendingItemsResult::fromArray(
            $this->client->request('session.queue.pendingItems', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Removes the most recently queued user-facing item (LIFO).
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function removeMostRecent(): QueueRemoveMostRecentResult
    {
        return QueueRemoveMostRecentResult::fromArray(
            $this->client->request('session.queue.removeMostRecent', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Clears all pending queued items on the local session.
     */
    public function clear(): void
    {
        $this->client->request('session.queue.clear', [
            'sessionId' => $this->sessionId,
        ]);
    }

    /**
     * Begins a native deferred-idle drain.
     *
     * @internal This method is part of the runtime orchestration surface.
     */
    public function beginDeferredIdleDrain(QueueBeginDeferredIdleDrainRequest|array $params): QueueBeginDeferredIdleDrainResult
    {
        $paramsArray = ($params instanceof QueueBeginDeferredIdleDrainRequest
            ? $params
            : QueueBeginDeferredIdleDrainRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return QueueBeginDeferredIdleDrainResult::fromArray(
            $this->client->request('session.queue.beginDeferredIdleDrain', $paramsArray),
        );
    }

    /**
     * Finishes a native deferred-idle drain.
     *
     * @internal This method is part of the runtime orchestration surface.
     */
    public function finishDeferredIdleDrain(QueueFinishDeferredIdleDrainRequest|array $params): QueueFinishDeferredIdleDrainResult
    {
        $paramsArray = ($params instanceof QueueFinishDeferredIdleDrainRequest
            ? $params
            : QueueFinishDeferredIdleDrainRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return QueueFinishDeferredIdleDrainResult::fromArray(
            $this->client->request('session.queue.finishDeferredIdleDrain', $paramsArray),
        );
    }
}
