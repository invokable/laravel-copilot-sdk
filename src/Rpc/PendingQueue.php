<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\QueueBeginDeferredIdleDrainRequest;
use Revolution\Copilot\Types\Rpc\QueueBeginDeferredIdleDrainResult;
use Revolution\Copilot\Types\Rpc\QueueDuplicateAtRequest;
use Revolution\Copilot\Types\Rpc\QueueDuplicateAtResult;
use Revolution\Copilot\Types\Rpc\QueueFinishDeferredIdleDrainRequest;
use Revolution\Copilot\Types\Rpc\QueueFinishDeferredIdleDrainResult;
use Revolution\Copilot\Types\Rpc\QueueInsertAtRequest;
use Revolution\Copilot\Types\Rpc\QueueInsertAtResult;
use Revolution\Copilot\Types\Rpc\QueueMoveItemRequest;
use Revolution\Copilot\Types\Rpc\QueueMoveItemResult;
use Revolution\Copilot\Types\Rpc\QueuePendingItemsResult;
use Revolution\Copilot\Types\Rpc\QueueRemoveAtRequest;
use Revolution\Copilot\Types\Rpc\QueueRemoveAtResult;
use Revolution\Copilot\Types\Rpc\QueueRemoveMostRecentResult;
use Revolution\Copilot\Types\Rpc\QueueSendNowRequest;
use Revolution\Copilot\Types\Rpc\QueueSendNowResult;
use Revolution\Copilot\Types\Rpc\QueueSetDrainPausedRequest;
use Revolution\Copilot\Types\Rpc\QueueUpdateTextRequest;
use Revolution\Copilot\Types\Rpc\QueueUpdateTextResult;

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
     * Inserts a queued message at a visible queue position.
     */
    public function insertAt(QueueInsertAtRequest|array $params): QueueInsertAtResult
    {
        $paramsArray = ($params instanceof QueueInsertAtRequest ? $params : QueueInsertAtRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return QueueInsertAtResult::fromArray(
            $this->client->request('session.queue.insertAt', $paramsArray),
        );
    }

    /**
     * Moves a queued item to another visible queue position.
     */
    public function moveItem(QueueMoveItemRequest|array $params): QueueMoveItemResult
    {
        $paramsArray = ($params instanceof QueueMoveItemRequest ? $params : QueueMoveItemRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return QueueMoveItemResult::fromArray(
            $this->client->request('session.queue.moveItem', $paramsArray),
        );
    }

    /**
     * Removes a queued item by its identifier.
     */
    public function removeAt(QueueRemoveAtRequest|array $params): QueueRemoveAtResult
    {
        $paramsArray = ($params instanceof QueueRemoveAtRequest ? $params : QueueRemoveAtRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return QueueRemoveAtResult::fromArray(
            $this->client->request('session.queue.removeAt', $paramsArray),
        );
    }

    /**
     * Duplicates a queued item by its identifier.
     */
    public function duplicateAt(QueueDuplicateAtRequest|array $params): QueueDuplicateAtResult
    {
        $paramsArray = ($params instanceof QueueDuplicateAtRequest ? $params : QueueDuplicateAtRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return QueueDuplicateAtResult::fromArray(
            $this->client->request('session.queue.duplicateAt', $paramsArray),
        );
    }

    /**
     * Immediately sends a queued item.
     */
    public function sendNow(QueueSendNowRequest|array $params): QueueSendNowResult
    {
        $paramsArray = ($params instanceof QueueSendNowRequest ? $params : QueueSendNowRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return QueueSendNowResult::fromArray(
            $this->client->request('session.queue.sendNow', $paramsArray),
        );
    }

    /**
     * Acquires or releases the queued-lane drain pause.
     *
     * Acquiring the pause is exclusive and non-idempotent: attempting to pause an already-paused session fails.
     * The pause is not released automatically when the caller exits; any caller can release it with `paused: false`.
     */
    public function setDrainPaused(QueueSetDrainPausedRequest|array $params): void
    {
        $paramsArray = ($params instanceof QueueSetDrainPausedRequest ? $params : QueueSetDrainPausedRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.queue.setDrainPaused', $paramsArray);
    }

    /**
     * Updates the prompt and optional display text of a queued item.
     */
    public function updateText(QueueUpdateTextRequest|array $params): QueueUpdateTextResult
    {
        $paramsArray = ($params instanceof QueueUpdateTextRequest ? $params : QueueUpdateTextRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return QueueUpdateTextResult::fromArray(
            $this->client->request('session.queue.updateText', $paramsArray),
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

    /**
     * Append a steering message to the session queue.
     *
     * @experimental This method is experimental and may change or be removed.
     */
    public function appendSteering(array $params): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request('session.queue.appendSteering', $params);
    }

    /**
     * Withdraw a queued message, optionally interrupting its active turn.
     *
     * @experimental This method is experimental and may change or be removed.
     */
    public function withdrawMessage(array $params): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request('session.queue.withdrawMessage', $params);
    }
}
