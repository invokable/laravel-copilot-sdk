<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\KerberosAuthSetProviderRequest;
use Revolution\Copilot\Types\Rpc\NetworkSetProviderRequest;

/**
 * Legacy Kerberos API alias for network callback registration.
 *
 * @deprecated Use PendingServerNetwork instead.
 */
class PendingServerKerberosAuth
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Register the Kerberos provider through the network RPC.
     *
     * @deprecated Use PendingServerNetwork::setProvider() instead.
     */
    public function setProvider(KerberosAuthSetProviderRequest|array $params): void
    {
        $legacyParams = ($params instanceof KerberosAuthSetProviderRequest
            ? $params
            : KerberosAuthSetProviderRequest::fromArray($params))->toArray();

        $legacyRequest = KerberosAuthSetProviderRequest::fromArray($legacyParams);

        (new PendingServerNetwork($this->client))->setProvider(new NetworkSetProviderRequest(
            scope: $legacyRequest->scope,
            authenticateNegotiate: true,
        ));
    }
}
