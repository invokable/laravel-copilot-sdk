<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\UserSettingsGetResult;
use Revolution\Copilot\Types\Rpc\UserSettingsSetRequest;
use Revolution\Copilot\Types\Rpc\UserSettingsSetResult;

/**
 * Pending server-level user settings RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerUserSettings
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Drops this runtime process's in-memory user settings cache so the next settings read observes disk.
     */
    public function reload(): void
    {
        $this->client->request('user.settings.reload', []);
    }

    /**
     * Lists every known user setting with its effective value, default, and whether it is at the default.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function get(): UserSettingsGetResult
    {
        return UserSettingsGetResult::fromArray(
            $this->client->request('user.settings.get', []),
        );
    }

    /**
     * Writes one or more user settings to settings.json, replacing each provided top-level key.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function set(UserSettingsSetRequest|array $params): UserSettingsSetResult
    {
        $paramsArray = ($params instanceof UserSettingsSetRequest ? $params : UserSettingsSetRequest::fromArray($params))->toArray();

        return UserSettingsSetResult::fromArray(
            $this->client->request('user.settings.set', $paramsArray),
        );
    }
}
