<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ManagedSettingsRecheckPolicyHelperRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsResolvedData;

/**
 * Session-managed settings RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingManagedSettings
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function get(): array
    {
        return $this->client->request('session.managedSettings.get', [
            'sessionId' => $this->sessionId,
        ]);
    }

    /**
     * Recheck the policy helper and return the session's effective managed settings.
     *
     * @experimental This method is experimental and may change or be removed.
     */
    public function recheckPolicyHelper(): ManagedSettingsResolvedData
    {
        $params = (new ManagedSettingsRecheckPolicyHelperRequest)->toArray();
        $params['sessionId'] = $this->sessionId;

        return ManagedSettingsResolvedData::fromArray(
            $this->client->request('session.managedSettings.recheckPolicyHelper', $params),
        );
    }
}
