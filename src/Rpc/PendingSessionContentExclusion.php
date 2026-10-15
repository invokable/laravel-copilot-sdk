<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ContentExclusionCheckPathsRequest;
use Revolution\Copilot\Types\Rpc\ContentExclusionCheckPathsResult;

/**
 * Session-scoped content-exclusion RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSessionContentExclusion
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Check local absolute paths within the session's working directory.
     *
     * @experimental
     */
    public function checkPaths(ContentExclusionCheckPathsRequest|array $params): ContentExclusionCheckPathsResult
    {
        $paramsArray = ($params instanceof ContentExclusionCheckPathsRequest ? $params : ContentExclusionCheckPathsRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ContentExclusionCheckPathsResult::fromArray(
            $this->client->request('session.contentExclusion.checkPaths', $paramsArray),
        );
    }
}
