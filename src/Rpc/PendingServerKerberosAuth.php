<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use InvalidArgumentException;
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
        $legacyRequest = ($params instanceof KerberosAuthSetProviderRequest
            ? $params
            : KerberosAuthSetProviderRequest::fromArray($params));

        if ($legacyRequest->mode === 'fallback') {
            throw new InvalidArgumentException(
                'kerberosAuth.mode "fallback" cannot be represented by the network API; use network() instead.',
            );
        }

        (new PendingServerNetwork($this->client))->setProvider(new NetworkSetProviderRequest(
            scope: $legacyRequest->scope,
            authenticateNegotiate: true,
        ));
    }
}
