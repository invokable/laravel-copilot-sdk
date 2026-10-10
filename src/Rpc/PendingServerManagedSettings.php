<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ManagedSettingsComposeRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsComposeResult;
use Revolution\Copilot\Types\Rpc\ManagedSettingsPermissionsEvaluateRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsPermissionsEvaluateResult;
use Revolution\Copilot\Types\Rpc\ManagedSettingsReadResult;
use Revolution\Copilot\Types\Rpc\ManagedSettingsResolveRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsResolveResult;
use Revolution\Copilot\Types\Rpc\ManagedSettingsSchemaResult;
use Revolution\Copilot\Types\Rpc\ManagedSettingsValidateRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsValidateResult;

/**
 * Server-level managed settings RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerManagedSettings
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Discover and validate device-managed settings without creating a session.
     */
    public function read(): ManagedSettingsReadResult
    {
        return ManagedSettingsReadResult::fromArray(
            $this->client->request('managedSettings.read', []),
        );
    }

    /** Resolve device and account policy without creating a session. */
    public function resolve(ManagedSettingsResolveRequest|array $params = []): ManagedSettingsResolveResult
    {
        $paramsArray = ($params instanceof ManagedSettingsResolveRequest ? $params : ManagedSettingsResolveRequest::fromArray($params))->toArray();

        return ManagedSettingsResolveResult::fromArray(
            $this->client->request('managedSettings.resolve', $paramsArray),
        );
    }

    /** Retrieve the authoring schema supported by this runtime. */
    public function schema(): ManagedSettingsSchemaResult
    {
        return ManagedSettingsSchemaResult::fromArray(
            $this->client->request('managedSettings.schema', []),
        );
    }

    /** Validate a candidate managed-settings document without applying it. */
    public function validate(ManagedSettingsValidateRequest|array $params): ManagedSettingsValidateResult
    {
        $paramsArray = ($params instanceof ManagedSettingsValidateRequest ? $params : ManagedSettingsValidateRequest::fromArray($params))->toArray();

        return ManagedSettingsValidateResult::fromArray(
            $this->client->request('managedSettings.validate', $paramsArray),
        );
    }

    /** Preview the effective settings produced by candidate policy documents. */
    public function compose(ManagedSettingsComposeRequest|array $params): ManagedSettingsComposeResult
    {
        $paramsArray = ($params instanceof ManagedSettingsComposeRequest ? $params : ManagedSettingsComposeRequest::fromArray($params))->toArray();

        return ManagedSettingsComposeResult::fromArray(
            $this->client->request('managedSettings.compose', $paramsArray),
        );
    }

    /**
     * Evaluate ordered operations against a supplied managed-permissions context.
     *
     * @experimental
     */
    public function evaluatePermissions(ManagedSettingsPermissionsEvaluateRequest|array $params): ManagedSettingsPermissionsEvaluateResult
    {
        $paramsArray = ($params instanceof ManagedSettingsPermissionsEvaluateRequest
            ? $params
            : ManagedSettingsPermissionsEvaluateRequest::fromArray($params))->toArray();

        return ManagedSettingsPermissionsEvaluateResult::fromArray(
            $this->client->request('managedSettings.permissions.evaluate', $paramsArray),
        );
    }
}
