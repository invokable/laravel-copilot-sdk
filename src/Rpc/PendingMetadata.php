<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\MetadataContextInfoRequest;
use Revolution\Copilot\Types\Rpc\MetadataContextInfoResult;
use Revolution\Copilot\Types\Rpc\MetadataIsProcessingResult;
use Revolution\Copilot\Types\Rpc\MetadataRecomputeContextTokensRequest;
use Revolution\Copilot\Types\Rpc\MetadataRecomputeContextTokensResult;
use Revolution\Copilot\Types\Rpc\MetadataRecordContextChangeRequest;
use Revolution\Copilot\Types\Rpc\MetadataRecordContextChangeResult;
use Revolution\Copilot\Types\Rpc\MetadataSetWorkingDirectoryRequest;
use Revolution\Copilot\Types\Rpc\MetadataSetWorkingDirectoryResult;
use Revolution\Copilot\Types\Rpc\SessionMetadataSnapshot;

/**
 * Pending session-scoped metadata RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingMetadata
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function snapshot(): SessionMetadataSnapshot
    {
        return SessionMetadataSnapshot::fromArray(
            $this->client->request('session.metadata.snapshot', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    public function isProcessing(): MetadataIsProcessingResult
    {
        return MetadataIsProcessingResult::fromArray(
            $this->client->request('session.metadata.isProcessing', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    public function contextInfo(MetadataContextInfoRequest|array $params): MetadataContextInfoResult
    {
        $paramsArray = ($params instanceof MetadataContextInfoRequest ? $params : MetadataContextInfoRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return MetadataContextInfoResult::fromArray(
            $this->client->request('session.metadata.contextInfo', $paramsArray),
        );
    }

    public function recordContextChange(MetadataRecordContextChangeRequest|array $params): MetadataRecordContextChangeResult
    {
        $paramsArray = ($params instanceof MetadataRecordContextChangeRequest ? $params : MetadataRecordContextChangeRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return MetadataRecordContextChangeResult::fromArray(
            $this->client->request('session.metadata.recordContextChange', $paramsArray),
        );
    }

    public function setWorkingDirectory(MetadataSetWorkingDirectoryRequest|array $params): MetadataSetWorkingDirectoryResult
    {
        $paramsArray = ($params instanceof MetadataSetWorkingDirectoryRequest ? $params : MetadataSetWorkingDirectoryRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return MetadataSetWorkingDirectoryResult::fromArray(
            $this->client->request('session.metadata.setWorkingDirectory', $paramsArray),
        );
    }

    public function recomputeContextTokens(MetadataRecomputeContextTokensRequest|array $params): MetadataRecomputeContextTokensResult
    {
        $paramsArray = ($params instanceof MetadataRecomputeContextTokensRequest ? $params : MetadataRecomputeContextTokensRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return MetadataRecomputeContextTokensResult::fromArray(
            $this->client->request('session.metadata.recomputeContextTokens', $paramsArray),
        );
    }
}
