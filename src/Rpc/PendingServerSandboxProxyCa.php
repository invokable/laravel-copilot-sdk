<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SandboxConfig;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaCreateResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaCreateForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaCreateForConfigDirResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaDeclineParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaGetDeclinedParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaGetStatusForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaGetStatusForConfigDirResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRemoveForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRemoveForConfigDirResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRotateForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRotateForConfigDirResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRotateIfUntrustedParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaStatus;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaTrustCertificateRequest;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaTrustCertificateResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaTrustForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaTrustForConfigDirResult;

/** Server-side sandbox credential-proxy certificate authority RPCs. */
class PendingServerSandboxProxyCa
{
    public function __construct(protected JsonRpcClient $client) {}

    public function getStatus(SandboxConfig|array|null $sandboxConfig = null): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray($this->client->request(
            'sandbox.proxyCa.getStatus',
            $this->params($sandboxConfig),
        ));
    }

    public function create(SandboxConfig|array|null $sandboxConfig = null): SandboxProxyCaCreateResult
    {
        return SandboxProxyCaCreateResult::fromArray($this->client->request(
            'sandbox.proxyCa.create',
            $this->params($sandboxConfig),
        ));
    }

    public function rotate(SandboxConfig|array|null $sandboxConfig = null): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray($this->client->request(
            'sandbox.proxyCa.rotate',
            $this->params($sandboxConfig),
        ));
    }

    public function trust(SandboxConfig|array|null $sandboxConfig = null): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray($this->client->request(
            'sandbox.proxyCa.trust',
            $this->params($sandboxConfig),
        ));
    }

    public function remove(): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray(
            $this->client->request('sandbox.proxyCa.remove', []),
        );
    }

    /**
     * Check whether this sandbox configuration is relevant to proxy CA management.
     *
     * @experimental
     */
    public function checkRelevance(SandboxConfig|array|null $sandboxConfig = null): bool
    {
        return (bool) $this->client->request(
            'sandbox.proxyCa.checkRelevance',
            $this->params($sandboxConfig),
        );
    }

    /**
     * Check whether this host supports installing the proxy CA.
     *
     * @experimental
     */
    public function checkInstallationSupport(): bool
    {
        return (bool) $this->client->request('sandbox.proxyCa.checkInstallationSupport', []);
    }

    /**
     * Gets the proxy CA status for a specific configuration directory.
     *
     * @experimental
     */
    public function getStatusForConfigDir(SandboxProxyCaGetStatusForConfigDirParams|array $params): SandboxProxyCaGetStatusForConfigDirResult
    {
        $params = $params instanceof SandboxProxyCaGetStatusForConfigDirParams
            ? $params
            : SandboxProxyCaGetStatusForConfigDirParams::fromArray($params);

        return SandboxProxyCaGetStatusForConfigDirResult::fromArray($this->client->request(
            'sandbox.proxyCa.getStatusForConfigDir',
            $params->toArray(),
        ));
    }

    /**
     * Creates the proxy CA for a specific configuration directory.
     *
     * @experimental
     */
    public function createForConfigDir(SandboxProxyCaCreateForConfigDirParams|array $params): SandboxProxyCaCreateForConfigDirResult
    {
        $params = $params instanceof SandboxProxyCaCreateForConfigDirParams
            ? $params
            : SandboxProxyCaCreateForConfigDirParams::fromArray($params);

        return SandboxProxyCaCreateForConfigDirResult::fromArray($this->client->request(
            'sandbox.proxyCa.createForConfigDir',
            $params->toArray(),
        ));
    }

    /**
     * Rotates the proxy CA for a specific configuration directory.
     *
     * @experimental
     */
    public function rotateForConfigDir(SandboxProxyCaRotateForConfigDirParams|array $params): SandboxProxyCaRotateForConfigDirResult
    {
        $params = $params instanceof SandboxProxyCaRotateForConfigDirParams
            ? $params
            : SandboxProxyCaRotateForConfigDirParams::fromArray($params);

        return SandboxProxyCaRotateForConfigDirResult::fromArray($this->client->request(
            'sandbox.proxyCa.rotateForConfigDir',
            $params->toArray(),
        ));
    }

    /**
     * Adds the proxy CA for a specific configuration directory to OS trust.
     *
     * @experimental
     */
    public function trustForConfigDir(SandboxProxyCaTrustForConfigDirParams|array $params): SandboxProxyCaTrustForConfigDirResult
    {
        $params = $params instanceof SandboxProxyCaTrustForConfigDirParams
            ? $params
            : SandboxProxyCaTrustForConfigDirParams::fromArray($params);

        return SandboxProxyCaTrustForConfigDirResult::fromArray($this->client->request(
            'sandbox.proxyCa.trustForConfigDir',
            $params->toArray(),
        ));
    }

    /**
     * Removes the proxy CA for a specific configuration directory from OS trust.
     *
     * @experimental
     */
    public function removeForConfigDir(SandboxProxyCaRemoveForConfigDirParams|array $params): SandboxProxyCaRemoveForConfigDirResult
    {
        $params = $params instanceof SandboxProxyCaRemoveForConfigDirParams
            ? $params
            : SandboxProxyCaRemoveForConfigDirParams::fromArray($params);

        return SandboxProxyCaRemoveForConfigDirResult::fromArray($this->client->request(
            'sandbox.proxyCa.removeForConfigDir',
            $params->toArray(),
        ));
    }

    /**
     * Checks whether proxy CA installation was declined in the supplied host context.
     *
     * @experimental
     */
    public function getDeclined(SandboxProxyCaGetDeclinedParams|array $params): bool
    {
        $params = $params instanceof SandboxProxyCaGetDeclinedParams
            ? $params
            : SandboxProxyCaGetDeclinedParams::fromArray($params);

        return (bool) $this->client->request(
            'sandbox.proxyCa.getDeclined',
            $params->toArray(),
        );
    }

    /**
     * Records that proxy CA installation was declined in the supplied host context.
     *
     * @experimental
     */
    public function decline(SandboxProxyCaDeclineParams|array $params): void
    {
        $params = $params instanceof SandboxProxyCaDeclineParams
            ? $params
            : SandboxProxyCaDeclineParams::fromArray($params);

        $this->client->request(
            'sandbox.proxyCa.decline',
            $params->toArray(),
        );
    }

    /**
     * Rotates the proxy CA only when it is not trusted.
     *
     * @experimental
     */
    public function rotateIfUntrusted(SandboxProxyCaRotateIfUntrustedParams|array $params): bool
    {
        $params = $params instanceof SandboxProxyCaRotateIfUntrustedParams
            ? $params
            : SandboxProxyCaRotateIfUntrustedParams::fromArray($params);

        return (bool) $this->client->request(
            'sandbox.proxyCa.rotateIfUntrusted',
            $params->toArray(),
        );
    }

    /**
     * Trusts a supplied proxy CA certificate.
     *
     * @experimental
     */
    public function trustCertificate(SandboxProxyCaTrustCertificateRequest|array $params): SandboxProxyCaTrustCertificateResult
    {
        $params = $params instanceof SandboxProxyCaTrustCertificateRequest
            ? $params
            : SandboxProxyCaTrustCertificateRequest::fromArray($params);

        return SandboxProxyCaTrustCertificateResult::fromArray($this->client->request(
            'sandbox.proxyCa.trustCertificate',
            $params->toArray(),
        ));
    }

    private function params(SandboxConfig|array|null $sandboxConfig): array
    {
        if ($sandboxConfig === null) {
            return [];
        }

        return [
            'sandboxConfig' => $sandboxConfig instanceof SandboxConfig
                ? $sandboxConfig->toArray()
                : $sandboxConfig,
        ];
    }
}
