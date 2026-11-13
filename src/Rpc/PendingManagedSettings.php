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
     * Read the current managed settings snapshot without waiting for policy application.
     *
     * @experimental This method is experimental and may change or be removed.
     */
    public function getCurrentSnapshot(): ?ManagedSettingsResolvedData
    {
        $snapshot = $this->client->request('session.managedSettings.getCurrentSnapshot', [
            'sessionId' => $this->sessionId,
        ]);

        return $snapshot === null ? null : ManagedSettingsResolvedData::fromArray($snapshot);
    }

    /**
     * Wait for the session's current policy import and application to finish.
     *
     * @experimental This method is experimental and may change or be removed.
     */
    public function awaitApplied(): void
    {
        $this->client->request('session.managedSettings.awaitApplied', [
            'sessionId' => $this->sessionId,
        ]);
    }

    /**
     * Wait for the current policy commit to be published.
     *
     * @experimental This method is experimental and may change or be removed.
     */
    public function awaitPublished(): void
    {
        $this->client->request('session.managedSettings.awaitPublished', [
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
