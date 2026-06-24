<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AccountAllUsers;
use Revolution\Copilot\Types\Rpc\AccountGetCurrentAuthResult;
use Revolution\Copilot\Types\Rpc\AccountGetQuotaRequest;
use Revolution\Copilot\Types\Rpc\AccountGetQuotaResult;
use Revolution\Copilot\Types\Rpc\AccountLoginRequest;
use Revolution\Copilot\Types\Rpc\AccountLoginResult;
use Revolution\Copilot\Types\Rpc\AccountLogoutRequest;
use Revolution\Copilot\Types\Rpc\AccountLogoutResult;

/**
 * Pending account RPC operations.
 */
class PendingServerAccount
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Get account quota.
     *
     * @param  AccountGetQuotaRequest|array|null  $params  Optional params. When gitHubToken is provided,
     *                                                     resolves that token for per-user quota instead of global auth.
     */
    public function getQuota(AccountGetQuotaRequest|array|null $params = null): AccountGetQuotaResult
    {
        $paramsArray = $params === null
            ? []
            : ($params instanceof AccountGetQuotaRequest ? $params : AccountGetQuotaRequest::fromArray($params))->toArray();

        return AccountGetQuotaResult::fromArray(
            $this->client->request('account.getQuota', $paramsArray),
        );
    }

    /**
     * Get the current authentication state.
     */
    public function getCurrentAuth(): AccountGetCurrentAuthResult
    {
        return AccountGetCurrentAuthResult::fromArray(
            $this->client->request('account.getCurrentAuth', []),
        );
    }

    /**
     * Get all authenticated users.
     *
     * @return AccountAllUsers[]
     */
    public function getAllUsers(): array
    {
        $result = $this->client->request('account.getAllUsers', []);

        return array_map(
            fn (array $user) => AccountAllUsers::fromArray($user),
            $result,
        );
    }

    /**
     * Store authentication credentials after successful login (e.g., device code flow).
     *
     * @param  AccountLoginRequest|array  $params  Credentials to store after successful authentication.
     */
    public function login(AccountLoginRequest|array $params): AccountLoginResult
    {
        $paramsArray = ($params instanceof AccountLoginRequest
            ? $params
            : AccountLoginRequest::fromArray($params))->toArray();

        return AccountLoginResult::fromArray(
            $this->client->request('account.login', $paramsArray),
        );
    }

    /**
     * Log out a user.
     *
     * @param  AccountLogoutRequest|array  $params  User to log out.
     */
    public function logout(AccountLogoutRequest|array $params): AccountLogoutResult
    {
        $paramsArray = ($params instanceof AccountLogoutRequest
            ? $params
            : AccountLogoutRequest::fromArray($params))->toArray();

        return AccountLogoutResult::fromArray(
            $this->client->request('account.logout', $paramsArray),
        );
    }
}
