<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\KerberosAuthSetProviderRequest;

/**
 * Pending server-level Kerberos authentication RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerKerberosAuth
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Register the Kerberos provider for this SDK connection.
     *
     * @experimental
     */
    public function setProvider(KerberosAuthSetProviderRequest|array $params): void
    {
        $paramsArray = ($params instanceof KerberosAuthSetProviderRequest
            ? $params
            : KerberosAuthSetProviderRequest::fromArray($params))->toArray();

        $this->client->request('kerberosAuth.setProvider', $paramsArray);
    }
}
