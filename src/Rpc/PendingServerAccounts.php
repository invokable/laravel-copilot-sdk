<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\Enums\EntraTokenInteraction;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\EntraTokenAcquireRequest;
use Revolution\Copilot\Types\Rpc\EntraTokenAcquireResult;

/** Experimental server-scoped account operations. */
class PendingServerAccounts
{
    public function __construct(protected JsonRpcClient $client) {}

    public function acquireEntraToken(EntraTokenAcquireRequest|array $request): EntraTokenAcquireResult
    {
        $request = $request instanceof EntraTokenAcquireRequest
            ? $request
            : new EntraTokenAcquireRequest(
                clientId: (string) ($request['clientId'] ?? ''),
                tenantId: (string) ($request['tenantId'] ?? ''),
                redirectUri: (string) ($request['redirectUri'] ?? ''),
                scopes: array_values($request['scopes'] ?? []),
                interaction: EntraTokenInteraction::tryFrom((string) ($request['interaction'] ?? ''))
                    ?? (string) ($request['interaction'] ?? ''),
                accessTokenToRenew: isset($request['accessTokenToRenew'])
                    ? (string) $request['accessTokenToRenew']
                    : null,
            );

        return EntraTokenAcquireResult::fromArray(
            $this->client->request('accounts.acquireEntraToken', $request->toArray()),
        );
    }
}
