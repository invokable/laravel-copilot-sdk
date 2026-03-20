<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionExtensionsDisableParams;
use Revolution\Copilot\Types\Rpc\SessionExtensionsEnableParams;
use Revolution\Copilot\Types\Rpc\SessionExtensionsListResult;

/**
 * Pending extensions RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingExtensions
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * List discovered extensions and their current status.
     */
    public function list(): SessionExtensionsListResult
    {
        return SessionExtensionsListResult::fromArray(
            $this->client->request('session.extensions.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Enable an extension.
     */
    public function enable(SessionExtensionsEnableParams|array $params): array
    {
        $paramsArray = ($params instanceof SessionExtensionsEnableParams ? $params : SessionExtensionsEnableParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.extensions.enable', $paramsArray);
    }

    /**
     * Disable an extension.
     */
    public function disable(SessionExtensionsDisableParams|array $params): array
    {
        $paramsArray = ($params instanceof SessionExtensionsDisableParams ? $params : SessionExtensionsDisableParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.extensions.disable', $paramsArray);
    }

    /**
     * Reload extensions.
     */
    public function reload(): array
    {
        return $this->client->request('session.extensions.reload', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
