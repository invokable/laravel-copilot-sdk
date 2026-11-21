<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AuthProviderDescriptor;

/** Experimental session authentication provider operations. */
class PendingSessionAuthProviders
{
    public function __construct(protected JsonRpcClient $client, protected string $sessionId) {}

    /** @return array<AuthProviderDescriptor> */
    public function list(): array
    {
        $result = $this->client->request('session.authProviders.list', ['sessionId' => $this->sessionId]);

        return array_map(AuthProviderDescriptor::fromArray(...), $result);
    }
}
