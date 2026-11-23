<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionMemoryImportRecordRequest;
use Revolution\Copilot\Types\Rpc\SessionMemoryImportRecordResult;

/**
 * Pending session memory RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingMemory
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Import one record through the session's cloud memory service.
     *
     * @experimental
     *
     * @param  SessionMemoryImportRecordRequest|array{scope: string, subject: string, fact: string, reason: string, citations: string}  $params
     */
    public function importRecord(SessionMemoryImportRecordRequest|array $params): SessionMemoryImportRecordResult
    {
        $paramsArray = ($params instanceof SessionMemoryImportRecordRequest
            ? $params
            : SessionMemoryImportRecordRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionMemoryImportRecordResult::fromArray(
            $this->client->request('session.memory.importRecord', $paramsArray),
        );
    }
}
