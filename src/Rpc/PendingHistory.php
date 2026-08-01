<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\HistoryCompactRequest;
use Revolution\Copilot\Types\Rpc\HistoryCompactResult;
use Revolution\Copilot\Types\Rpc\HistoryListRewindPointsResult;
use Revolution\Copilot\Types\Rpc\HistoryPreviewRewindRequest;
use Revolution\Copilot\Types\Rpc\HistoryPreviewRewindResult;
use Revolution\Copilot\Types\Rpc\HistoryRewindRequest;
use Revolution\Copilot\Types\Rpc\HistoryRewindResult;
use Revolution\Copilot\Types\Rpc\HistoryTruncateRequest;
use Revolution\Copilot\Types\Rpc\HistoryTruncateResult;

/**
 * Pending history RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingHistory
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Compact the session history.
     */
    public function compact(HistoryCompactRequest|array|null $params = null): HistoryCompactResult
    {
        $paramsArray = $params === null
            ? []
            : ($params instanceof HistoryCompactRequest ? $params : HistoryCompactRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return HistoryCompactResult::fromArray(
            $this->client->request('session.history.compact', $paramsArray),
        );
    }

    /**
     * Truncate session history to a specific event.
     *
     * This event and all events after it are removed from the session.
     */
    public function truncate(HistoryTruncateRequest|array $params): HistoryTruncateResult
    {
        $paramsArray = ($params instanceof HistoryTruncateRequest ? $params : HistoryTruncateRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return HistoryTruncateResult::fromArray(
            $this->client->request('session.history.truncate', $paramsArray),
        );
    }

    /**
     * Lists the user turns that this session can rewind to.
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function listRewindPoints(): HistoryListRewindPointsResult
    {
        return HistoryListRewindPointsResult::fromArray(
            $this->client->request('session.history.listRewindPoints', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Previews the files that a conversation-and-files rewind would restore.
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function previewRewind(HistoryPreviewRewindRequest|array $params): HistoryPreviewRewindResult
    {
        $paramsArray = ($params instanceof HistoryPreviewRewindRequest
            ? $params
            : HistoryPreviewRewindRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return HistoryPreviewRewindResult::fromArray(
            $this->client->request('session.history.previewRewind', $paramsArray),
        );
    }

    /**
     * Rewinds session conversation, optionally restoring files from discarded turns.
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function rewind(HistoryRewindRequest|array $params): HistoryRewindResult
    {
        $paramsArray = ($params instanceof HistoryRewindRequest
            ? $params
            : HistoryRewindRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return HistoryRewindResult::fromArray(
            $this->client->request('session.history.rewind', $paramsArray),
        );
    }
}
