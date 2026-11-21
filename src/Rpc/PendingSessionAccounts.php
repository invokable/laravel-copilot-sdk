<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\Enums\LoginProviderKind;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\Account;
use Revolution\Copilot\Types\Rpc\AccountsGetActiveResult;
use Revolution\Copilot\Types\Rpc\AccountsListRequest;
use Revolution\Copilot\Types\Rpc\AccountsLogoutRequest;
use Revolution\Copilot\Types\Rpc\AccountsSetCredentialsRequest;
use Revolution\Copilot\Types\Rpc\AccountsSwitchActiveRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginAdvanceRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginBeginRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginBegun;
use Revolution\Copilot\Types\Rpc\AuthLoginCancelRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginIdentity;
use Revolution\Copilot\Types\Rpc\AuthLoginListRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginStep;
use Revolution\Copilot\Types\Rpc\AuthValidationError;

/** Experimental session-scoped account and interactive login operations. */
class PendingSessionAccounts
{
    public function __construct(protected JsonRpcClient $client, protected string $sessionId) {}

    /** @return array<Account> */
    public function list(AccountsListRequest|array|null $request = null): array
    {
        $params = $request instanceof AccountsListRequest ? $request->toArray() : ($request ?? []);
        $result = $this->client->request('session.accounts.list', [...$params, 'sessionId' => $this->sessionId]);

        return array_map(Account::fromArray(...), $result);
    }

    public function getActive(): AccountsGetActiveResult
    {
        return AccountsGetActiveResult::fromArray($this->client->request('session.accounts.getActive', ['sessionId' => $this->sessionId]));
    }

    /** @return array<AuthValidationError> */
    public function lastErrors(): array
    {
        return array_map(AuthValidationError::fromArray(...), $this->client->request('session.accounts.lastErrors', ['sessionId' => $this->sessionId]));
    }

    public function switchActive(AccountsSwitchActiveRequest|array $request): void
    {
        $params = $request instanceof AccountsSwitchActiveRequest ? $request->toArray() : $request;
        $this->client->request('session.accounts.switchActive', [...$params, 'sessionId' => $this->sessionId]);
    }

    public function logout(AccountsLogoutRequest|array $request): bool
    {
        $params = $request instanceof AccountsLogoutRequest ? $request->toArray() : $request;

        return $this->client->request('session.accounts.logout', [...$params, 'sessionId' => $this->sessionId]);
    }

    public function setCredentials(AccountsSetCredentialsRequest|array $request): void
    {
        $params = $request instanceof AccountsSetCredentialsRequest ? $request->toArray() : $request;
        $this->client->request('session.accounts.setCredentials', [...$params, 'sessionId' => $this->sessionId]);
    }

    /** @return array<AuthLoginIdentity> */
    public function loginList(AuthLoginListRequest|array $request): array
    {
        $params = $request instanceof AuthLoginListRequest ? $request->toArray() : $request;
        $result = $this->client->request('session.accounts.login.list', [...$params, 'sessionId' => $this->sessionId]);

        return array_map(AuthLoginIdentity::fromArray(...), $result);
    }

    public function begin(AuthLoginBeginRequest|LoginProviderKind|string $request): AuthLoginBegun
    {
        $request = $request instanceof AuthLoginBeginRequest ? $request : new AuthLoginBeginRequest($request);

        return AuthLoginBegun::fromArray($this->client->request('session.accounts.login.begin', ['sessionId' => $this->sessionId, ...$request->toArray()]));
    }

    public function advance(AuthLoginAdvanceRequest|array $request): AuthLoginStep
    {
        $request = $request instanceof AuthLoginAdvanceRequest ? $request : new AuthLoginAdvanceRequest($request['flowId'] ?? '', $request['input'] ?? null);

        return AuthLoginStep::fromArray($this->client->request('session.accounts.login.advance', ['sessionId' => $this->sessionId, ...$request->toArray()]));
    }

    public function cancel(AuthLoginCancelRequest|string $request): void
    {
        $request = $request instanceof AuthLoginCancelRequest ? $request : new AuthLoginCancelRequest($request);
        $this->client->request('session.accounts.login.cancel', ['sessionId' => $this->sessionId, ...$request->toArray()]);
    }
}
