<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionDynamicContextGetConfigResponse;
use Revolution\Copilot\Types\Rpc\SessionDynamicContextSetConfigRequest;

/**
 * Pending session dynamic-context RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSessionDynamicContext
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get the dynamic-context configuration associated with this session.
     *
     * @experimental
     */
    public function getConfig(): SessionDynamicContextGetConfigResponse
    {
        return SessionDynamicContextGetConfigResponse::fromArray(
            $this->client->request('session.dynamicContext.getConfig', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Set the dynamic-context configuration for this session.
     *
     * @experimental
     *
     * @param  SessionDynamicContextSetConfigRequest|array{storePath: string, repository: string, branch: string}  $params
     */
    public function setConfig(SessionDynamicContextSetConfigRequest|array $params): void
    {
        $paramsArray = ($params instanceof SessionDynamicContextSetConfigRequest
            ? $params
            : SessionDynamicContextSetConfigRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.dynamicContext.setConfig', $paramsArray);
    }
}
