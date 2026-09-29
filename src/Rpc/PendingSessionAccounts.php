<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\Enums\LoginProviderKind;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AccountStatus;
use Revolution\Copilot\Types\Rpc\AuthLoginAdvanceRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginBeginRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginBegun;
use Revolution\Copilot\Types\Rpc\AuthLoginCancelRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginStep;
use Revolution\Copilot\Types\Rpc\AuthReadValue;
use Revolution\Copilot\Types\Rpc\AuthWrite;
use Revolution\Copilot\Types\Rpc\AuthWriteResult;
use Revolution\Copilot\Types\Rpc\ProviderDescriptor;

/** Experimental session-scoped account and interactive login operations. */
class PendingSessionAccounts
{
    public function __construct(protected JsonRpcClient $client, protected string $sessionId) {}

    /** @return array<AccountStatus|ProviderDescriptor> */
    public function enumerate(string $kind = 'accounts', ?bool $brokerAvailable = null): array
    {
        $query = ['kind' => $kind];
        if ($kind === 'providers' && $brokerAvailable !== null) {
            $query['brokerAvailable'] = $brokerAvailable;
        }
        $result = $this->client->request('session.accounts.enumerate', ['sessionId' => $this->sessionId, 'query' => $query]);

        return array_map(fn (array $item) => $kind === 'providers' ? ProviderDescriptor::fromArray($item) : AccountStatus::fromArray($item), $result['items'] ?? []);
    }

    public function get(string $kind): AuthReadValue
    {
        $result = $this->client->request('session.accounts.get', ['sessionId' => $this->sessionId, 'query' => ['kind' => $kind]]);

        return AuthReadValue::fromArray($result);
    }

    public function set(AuthWrite|array $command): AuthWriteResult
    {
        $command = $command instanceof AuthWrite ? $command->toArray() : $command;

        return AuthWriteResult::fromArray($this->client->request('session.accounts.set', ['sessionId' => $this->sessionId, 'command' => $command]));
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
