<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\NameGetResult;
use Revolution\Copilot\Types\Rpc\NameSetRequest;

/**
 * Pending name RPC operations for a session.
 */
class PendingName
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get the session name.
     */
    public function get(): NameGetResult
    {
        return NameGetResult::fromArray(
            $this->client->request('session.name.get', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Set the session name.
     *
     * @param  NameSetRequest|array{name: string}  $params
     */
    public function set(NameSetRequest|array $params): void
    {
        $paramsArray = ($params instanceof NameSetRequest ? $params : NameSetRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.name.set', $paramsArray);
    }
}
