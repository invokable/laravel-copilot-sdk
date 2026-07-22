<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\FactoryAckResult;
use Revolution\Copilot\Types\Rpc\FactoryJournalGetRequest;
use Revolution\Copilot\Types\Rpc\FactoryJournalGetResult;
use Revolution\Copilot\Types\Rpc\FactoryJournalPutRequest;

/**
 * Pending factory journal RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingFactoryJournal
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Read a memoized factory journal entry.
     */
    public function get(FactoryJournalGetRequest|array $params): FactoryJournalGetResult
    {
        $paramsArray = ($params instanceof FactoryJournalGetRequest ? $params : FactoryJournalGetRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FactoryJournalGetResult::fromArray(
            $this->client->request('session.factory.journal.get', $paramsArray),
        );
    }

    /**
     * Store a memoized factory journal entry.
     */
    public function put(FactoryJournalPutRequest|array $params): FactoryAckResult
    {
        $paramsArray = ($params instanceof FactoryJournalPutRequest ? $params : FactoryJournalPutRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FactoryAckResult::fromArray(
            $this->client->request('session.factory.journal.put', $paramsArray),
        );
    }
}
