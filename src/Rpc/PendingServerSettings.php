<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SettingsRedactValueForDisplayRequest;
use Revolution\Copilot\Types\Rpc\SettingsRedactValueForDisplayResult;
use Revolution\Copilot\Types\Rpc\SettingsRemoveRawKeyRequest;
use Revolution\Copilot\Types\Rpc\SettingsRemoveRawKeyResult;

/**
 * Pending server-level settings file RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerSettings
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Removes one key from a settings file, leaving every other key untouched.
     *
     * Takes the key exactly as written, so it can delete a key the loader refused. Refuses rather than overwrites when the file does not parse.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function removeRawKey(SettingsRemoveRawKeyRequest|array $params): SettingsRemoveRawKeyResult
    {
        $paramsArray = ($params instanceof SettingsRemoveRawKeyRequest ? $params : SettingsRemoveRawKeyRequest::fromArray($params))->toArray();

        return SettingsRemoveRawKeyResult::fromArray(
            $this->client->request('settings.removeRawKey', $paramsArray),
        );
    }

    /**
     * Replaces the secret-bearing parts of a settings value with a redacted placeholder, so it can be shown to a user or written to a log.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function redactValueForDisplay(SettingsRedactValueForDisplayRequest|array $params): SettingsRedactValueForDisplayResult
    {
        $paramsArray = ($params instanceof SettingsRedactValueForDisplayRequest ? $params : SettingsRedactValueForDisplayRequest::fromArray($params))->toArray();

        return SettingsRedactValueForDisplayResult::fromArray(
            $this->client->request('settings.redactValueForDisplay', $paramsArray),
        );
    }
}
