<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ExtensionList;
use Revolution\Copilot\Types\Rpc\ExtensionsDisableRequest;
use Revolution\Copilot\Types\Rpc\ExtensionsEnableRequest;

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
    public function list(): ExtensionList
    {
        return ExtensionList::fromArray(
            $this->client->request('session.extensions.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Enable an extension.
     */
    public function enable(ExtensionsEnableRequest|array $params): array
    {
        $paramsArray = ($params instanceof ExtensionsEnableRequest ? $params : ExtensionsEnableRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.extensions.enable', $paramsArray);
    }

    /**
     * Disable an extension.
     */
    public function disable(ExtensionsDisableRequest|array $params): array
    {
        $paramsArray = ($params instanceof ExtensionsDisableRequest ? $params : ExtensionsDisableRequest::fromArray($params))->toArray();
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
